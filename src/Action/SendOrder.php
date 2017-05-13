<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Action;

use Fig\Http\Message\StatusCodeInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use MVLabs\EsCqrsWorkshop\Domain\Command\AddOrder as AddOrderCommand;
use Prooph\ServiceBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

final class SendOrder implements MiddlewareInterface
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
        $customerName = $request->getParsedBody()['customer'];
        $pizzeriaId = $request->getParsedBody()['pizzeria'];
        $pizzaTaste = $request->getParsedBody()['pizza'];

        $this->commandBus->dispatch(AddOrderCommand::fromCustomerNamePizzeriaAndPizzaTaste($customerName, $pizzeriaId, $pizzaTaste));

        return (new Response())->withStatus(StatusCodeInterface::STATUS_ACCEPTED);
    }
}
