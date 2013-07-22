<?php

namespace Restorando\HttpClient\Listener;

use Buzz\Listener\ListenerInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Restorando\Exception\ApiLimitExceedException;
use Restorando\Exception\ErrorException;
use Restorando\Exception\RuntimeException;
use Restorando\Exception\ValidationFailedException;

/**
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class ErrorListener implements ListenerInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function preSend(RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function postSend(RequestInterface $request, MessageInterface $response)
    {
        /** @var $response \Restorando\HttpClient\Message\Response */
        if ($response->isClientError() || $response->isServerError()) {
            $remaining = $response->getHeader('X-RateLimit-Remaining');
            if (null !== $remaining && 1 > $remaining) {
                throw new ApiLimitExceedException($this->options['api_limit']);
            }

            $content = $response->getContent();
            if (is_array($content) && isset($content['message'])) {
                if (400 == $response->getStatusCode()) {
                    throw new ErrorException($content['message'], 400);
                } elseif (422 == $response->getStatusCode() && isset($content['errors'])) {
                    $errors = array();
                    foreach ($content['errors'] as $error) {
                        switch ($error['code']) {
                            case 'missing_field':
                                $errors[] = sprintf('Field "%s" is missing', $error['field']);
                                break;

                            case 'invalid':
                                $errors[] = sprintf('Field "%s" is invalid', $error['field']);
                                break;

                            default:
                                $errors[] = $error['message'];
                                break;

                        }
                    }

                    throw new ValidationFailedException('Validation Failed: ' . implode(', ', $errors), 422, $content['errors']);
                }
            }

            throw new RuntimeException(isset($content['message']) ? $content['message'] : $content, $response->getStatusCode());
        }
    }
}
