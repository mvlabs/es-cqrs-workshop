<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Action;

use Fig\Http\Message\StatusCodeInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use MVLabs\EsCqrsWorkshop\Domain\Command\CreatePizzeria as CreatePizzeriaCommand;
use Prooph\ServiceBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

final class CreatePizzeria implements MiddlewareInterface
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
        $pizzaName = $request->getParsedBody()['name'];

        $this->commandBus->dispatch(CreatePizzeriaCommand::fromName($pizzaName));

        return (new Response())->withStatus(StatusCodeInterface::STATUS_ACCEPTED);
    }
}
