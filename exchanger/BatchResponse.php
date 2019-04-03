<?php

namespace exchanger;

class BatchResponse
{
    private $responses = [];

    /**
     * @param Response $response
     */
    public function add(Response $response): void {
        $this->responses[] = $response;
    }

    /**
     * @return Response[]
     */
    public function getResponses(): array {
        return $this->responses;
    }
}