<?php

declare(strict_types=1);

namespace BestIt\CommercetoolsODM\Repository;

use BestIt\CommercetoolsODM\Exception\APIException;
use BestIt\CommercetoolsODM\Exception\ResponseException;
use BestIt\CommercetoolsODM\Model\DefaultRepository;
use Commercetools\Core\Model\Cart\Cart;
use Commercetools\Core\Model\Order\ImportOrder;
use Commercetools\Core\Model\Order\Order;
use Commercetools\Core\Request\Orders\OrderCreateFromCartRequest;
use Commercetools\Core\Request\Orders\OrderImportRequest;
use Commercetools\Core\Response\ErrorResponse;

/**
 * Repository for orders.
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CommercetoolsODM\Repository
 */
class OrderRepository extends DefaultRepository implements OrderRepositoryInterface
{
    /**
     * Creates an order frmo a cart.
     * @param Cart $cart
     * @return Order
     */
    public function createFromCart(Cart $cart): Order
    {
        $documentManager = $this->getDocumentManager();

        $request = $documentManager->createRequest(
            $this->getClassName(),
            OrderCreateFromCartRequest::class,
            $cart->getId(),
            $cart->getVersion()
        );

        /** @var Order $order */
        list($order) = $this->processQuery($request);

        $documentManager->getUnitOfWork()->registerAsManaged($order, $order->getId(), $order->getVersion());

        return $order;
    }

    /**
     * Removes the given order.
     * @param Order $order The order.
     * @param bool $direct Should the deletion be deleted directly with a doc manager flush?
     * @return void
     */
    public function deleteOrder(Order $order, bool $direct = true)
    {
        $documentManager = $this->getDocumentManager();
        $documentManager->remove($order);

        if ($direct) {
            $documentManager->flush();
        }
    }

    /**
     * Imports the given order.
     * @param Order $importableOrder
     * @return Order
     * @throws ResponseException
     */
    public function import(Order $importableOrder): Order
    {
        $documentManager = $this->getDocumentManager();

        $request = $documentManager->createRequest(
            $this->getClassName(),
            OrderImportRequest::class,
            ImportOrder::fromArray($importableOrder->toArray())
        );

        /** @var Order $importedOrder */
        list($importedOrder, $response) = $this->processQuery($request);

        if ($response instanceof ErrorResponse) {
            throw APIException::fromResponse($response);
        }

        $documentManager->getUnitOfWork()->registerAsManaged(
            $importedOrder,
            $importedOrder->getId(),
            $importedOrder->getVersion()
        );

        return $importedOrder;
    }

    /**
     * Saves the given order.
     * @param Order $order
     * @param bool $direct Should the order be saved directly?
     */
    public function save(Order $order, bool $direct = true)
    {
        $this->getDocumentManager()->persist($order);

        if ($direct) {
            $this->getDocumentManager()->flush();
        }
    }
}
