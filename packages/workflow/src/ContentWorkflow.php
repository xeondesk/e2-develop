<?php
declare(strict_types=1);

namespace Nexo\Workflow;

final class ContentWorkflow
{
    public static function definition(): WorkflowDefinition
    {
        return new WorkflowDefinition(
            'content',
            ['draft', 'review', 'approved', 'scheduled', 'published', 'archived', 'trashed'],
            [
                'submit' => ['from' => ['draft'], 'to' => 'review', 'permission' => 'content.write'],
                'approve' => ['from' => ['review'], 'to' => 'approved', 'permission' => 'content.publish'],
                'reject' => ['from' => ['review'], 'to' => 'draft', 'permission' => 'content.write'],
                'schedule' => ['from' => ['approved'], 'to' => 'scheduled', 'permission' => 'content.publish'],
                'publish' => ['from' => ['approved', 'scheduled'], 'to' => 'published', 'permission' => 'content.publish'],
                'archive' => ['from' => ['published', 'scheduled', 'approved'], 'to' => 'archived', 'permission' => 'content.publish'],
                'trash' => ['from' => ['draft', 'review', 'approved', 'scheduled', 'published', 'archived'], 'to' => 'trashed', 'permission' => 'content.delete'],
                'restore' => ['from' => ['trashed'], 'to' => 'draft', 'permission' => 'content.write'],
            ],
            'draft'
        );
    }
}
