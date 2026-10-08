<?php
declare(strict_types=1);

namespace Nexo\Workflow;

final class WorkflowDefinition
{
    /**
     * @param string[] $states
     * @param array<string, array{from: string[], to: string, permission?: string}> $transitions
     */
    public function __construct(
        private string $name,
        private array $states,
        private array $transitions,
        private string $initialState
    ) {
        if (!in_array($initialState, $states, true)) {
            throw new \InvalidArgumentException("Initial state '{$initialState}' is not a known state");
        }
        foreach ($transitions as $name => $t) {
            if (!in_array($t['to'], $states, true)) {
                throw new \InvalidArgumentException("Transition '{$name}' targets unknown state '{$t['to']}'");
            }
            foreach ($t['from'] as $from) {
                if (!in_array($from, $states, true)) {
                    throw new \InvalidArgumentException("Transition '{$name}' starts from unknown state '{$from}'");
                }
            }
        }
    }

    public function name(): string { return $this->name; }
    /** @return string[] */
    public function states(): array { return $this->states; }
    /** @return array<string, array{from: string[], to: string, permission?: string}> */
    public function transitions(): array { return $this->transitions; }
    public function initialState(): string { return $this->initialState; }

    public function transitionTarget(string $currentState, string $transitionName): ?string
    {
        $t = $this->transitions[$transitionName] ?? null;
        if ($t === null || !in_array($currentState, $t['from'], true)) {
            return null;
        }
        return $t['to'];
    }

    public function transitionPermission(string $transitionName): ?string
    {
        return $this->transitions[$transitionName]['permission'] ?? null;
    }

    /** @return string[] */
    public function transitionsFrom(string $state): array
    {
        $names = [];
        foreach ($this->transitions as $name => $t) {
            if (in_array($state, $t['from'], true)) {
                $names[] = $name;
            }
        }
        return $names;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'states' => $this->states,
            'transitions' => $this->transitions,
            'initial' => $this->initialState,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['states'], $data['transitions'], $data['initial']);
    }
}
