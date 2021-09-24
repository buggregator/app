<?php
declare(strict_types=1);

namespace Modules\Ray\Console;

use App\Attributes\Console\Stream;
use Interfaces\Console\Handler;
use Modules\Ray\Console\Handlers\DebugHandler;
use Symfony\Component\Console\Output\OutputInterface;

#[Stream(name: 'ray')]
class StreamHandler implements Handler
{
    private array $payloadHandlers = [];

    public function __construct(
        private StreamHandlerConfig $config,
        private OutputInterface     $output
    )
    {
        $this->payloadHandlers = $config->getHandlers();
    }

    public function handle(array $stream): void
    {
        foreach ($stream['data']['payloads'] as $payload) {
            if (!isset($this->payloadHandlers[$payload['type']])) {
                continue;

                $handler = new DebugHandler($this->output);
            } else {
                $handler = $this->payloadHandlers[$payload['type']];
                $handler = new $handler($this->output);
            }

            if ($handler->shouldBeSkipped($payload)) {
                continue;
            }

            $handler->printContext($payload);
            $handler->printTitle($payload);
            $handler->handle($payload);
        }
    }

    public function shouldBeSkipped(array $stream): bool
    {
        if (!$this->config->isEnabled()) {
            return true;
        }

        return !isset($stream['data']['payloads']);
    }
}