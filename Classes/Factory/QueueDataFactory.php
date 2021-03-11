<?php

namespace Mediatis\Formrelay\Factory;

use FormRelay\Core\Factory\QueueDataFactory as OriginalQueueDataFactory;
use FormRelay\Core\Model\Submission\SubmissionInterface;

class QueueDataFactory extends OriginalQueueDataFactory
{
    public function pack(SubmissionInterface $submission): array
    {
        $packed = parent::pack($submission);
        $packed['repository']['pid'] = $submission->getConfiguration()->get('pid', 0);
        return $packed;
    }
}
