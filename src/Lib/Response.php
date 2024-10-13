<?php

namespace Fenrir\Framework\Lib;

use Closure;
use Generator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class Response extends HttpResponse
{
    protected ?Closure $callback = null;
    protected bool $streamed = false;

    /**
     * Summary of json
     * @param mixed $data
     * @return Response
     */
    public function json($data)
    {
        if ($data instanceof Generator) {
            $data = iterator_to_array($data);
        }

        $this->setContent(json_encode($data));
        $this->setContentType("application/json");
        return $this;
    }

    public function setContentType(string $content_type)
    {
        $this->headers->set("Content-Type", $content_type);
        return $this;
    }

    public function setCallback(?callable $callback): static
    {
        $this->callback = $callback(...);
        return $this;
    }

    public function getCallback(): ?Closure
    {
        if (!$this->callback) {
            return null;
        }
        return ($this->callback)(...);
    }

    public function sendContent(): static
    {
        if ($this->streamed) {
            return $this;
        }
        if (null === $this->callback) {
            return parent::sendContent();
        }
        ($this->callback)();
        return $this;
    }

    public function setResource($resource): static
    {
        $this->setCallback(function () use ($resource) {
            fpassthru($resource);
        });

        return $this;
    }
}
