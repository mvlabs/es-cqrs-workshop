<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use MVLabs\EsCqrsWorkshop\Domain\ProjectionReader\PizzeriasReaderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

final class PizzeriasList implements MiddlewareInterface
{

    /**
     * @var PizzeriasReaderInterface
     */
    private $pizzeriasReader;

    public function __construct(PizzeriasReaderInterface $pizzeriasReader)
    {
        $this->pizzeriasReader = $pizzeriasReader;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        return new TextResponse(json_encode($this->pizzeriasReader->listPizzerias()));
    }
}
