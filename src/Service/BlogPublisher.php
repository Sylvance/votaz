<?php
namespace App\Service;

use App\Entity\BlogPost;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;

class BlogPublisher
{
    public function __construct(
        #[Target('blog_publishing')] private WorkflowInterface $workflow,
    ) {
    }
    public function publish(BlogPost $post): void
    {
        if ($this->workflow->can($post, 'publish')) {
            // updates $post->status after checking the transition
            // is valid from the current state
            $this->workflow->apply($post, 'publish');
        }
    }
}
