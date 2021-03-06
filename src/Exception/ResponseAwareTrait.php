<?php

namespace BestIt\CommercetoolsODM\Exception;

use Commercetools\Core\Response\ApiResponseInterface;
use Commercetools\Core\Response\ErrorResponse;

/**
 * Helps with responses in the exceptions.
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CommercetoolsODM
 * @subpackage Exception
 * @version $id$
 */
trait ResponseAwareTrait
{
    /**
     * The correlation id for the request.
     * @var string
     */
    private $correlationId = '';

    /**
     * The response.
     * @var ErrorResponse
     */
    private $response = null;

    /**
     * Returns the correlation id for the request.
     * @return string
     */
    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    /**
     * Returns the used response.
     * @return ApiResponseInterface
     */
    public function getResponse(): ApiResponseInterface
    {
        return $this->response;
    }

    /**
     * Sets the correlation id for the request.
     * @param string $correlationId
     * @return ResponseAwareTrait
     */
    public function setCorrelationId(string $correlationId)
    {
        $this->correlationId = $correlationId;
        return $this;
    }

    /**
     * Sets the response.
     * @param ErrorResponse $response
     * @return $this
     */
    public function setResponse(ErrorResponse $response)
    {
        $this->response = $response;

        return $this;
    }
}
