<?php
declare(strict_types=1);

namespace Nexo\Tests;

use Nexo\Workflow\ContentWorkflow;
use Nexo\Workflow\WorkflowDefinition;
use Nexo\Workflow\WorkflowEngine;
use PHPUnit\Framework\TestCase;

final class WorkflowTest extends TestCase
{
    public function testValidTransition(): void
    {
        $engine = new WorkflowEngine();
        $def = ContentWorkflow::definition();

        $result = $engine->transition($def, 'draft', 'submit', 'entry-1');
        self::assertTrue($result->isOk());
        self::assertSame('review', $result->unwrap());
    }

    public function testInvalidTransition(): void
    {
        $engine = new WorkflowEngine();
        $result = $engine->transition(ContentWorkflow::definition(), 'published', 'submit', 'entry-1');
        self::assertTrue($result->isErr());
    }

    public function testTransitionsFromState(): void
    {
        $def = ContentWorkflow::definition();
        self::assertContains('trash', $def->transitionsFrom('draft'));
        self::assertContains('publish', $def->transitionsFrom('approved'));
    }

    public function testDefinitionRejectsUnknownStates(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new WorkflowDefinition('x', ['a'], ['go' => ['from' => ['a'], 'to' => 'b']], 'a');
    }
}
