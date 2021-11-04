<?php

namespace Mediatis\Formrelay\Domain\Repository\Queue;

use DateTime;
use FormRelay\Core\Queue\JobInterface;
use FormRelay\Core\Queue\QueueInterface;
use Mediatis\Formrelay\Domain\Model\Queue\Job;
use TYPO3\CMS\Extbase\Persistence\Repository;

class JobRepository extends Repository implements QueueInterface
{
    protected $pid = 0;

    public function setPid(int $pid)
    {
        $this->pid = $pid;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    protected function fetchWhere(array $status = [], int $limit = 0, int $offset = 0, int $minTimeSinceChangedInSeconds = 0, int $minAgeInSeconds = 0)
    {
        $query = $this->createQuery();
        if ($this->pid) {
            $query->getQuerySettings()->setRespectStoragePage(true);
            $query->getQuerySettings()->setStoragePageIds([$this->pid]);
        } else {
            $query->getQuerySettings()->setRespectStoragePage(false);
        }

        $conditions = [];
        if (count($status) > 0) {
            $conditions[] = $query->in('status', $status);
        }
        if ($minTimeSinceChangedInSeconds > 0) {
            $then = new DateTime();
            $then->modify('- ' . $minTimeSinceChangedInSeconds . ' seconds');
            $conditions[] = $query->lessThan('changed', $then);
        }
        if ($minAgeInSeconds > 0) {
            $then = new DateTime();
            $then->modify('- ' . $minAgeInSeconds . ' seconds');
            $conditions[] = $query->lessThan('created', $then);
        }
        if (count($conditions) > 0) {
            $query->matching($query->logicalAnd($conditions));
        }
        if ($limit > 0) {
            $query->setLimit($limit);
        }
        if ($offset > 0) {
            $query->setOffset($offset);
        }
        return $query->execute()->toArray();
    }

    public function fetch(array $status = [], int $limit = 0, int $offset = 0)
    {
        return $this->fetchWhere($status, $limit, $offset);
    }

    public function fetchPending(int $limit = 0, int $offset = 0)
    {
        return $this->fetchWhere([QueueInterface::STATUS_PENDING], $limit, $offset);
    }

    public function fetchRunning(int $limit = 0, int $offset = 0, int $minTimeSinceChangedInSeconds = 0)
    {
        return $this->fetchWhere([QueueInterface::STATUS_RUNNING], $limit, $offset, $minTimeSinceChangedInSeconds);
    }

    public function fetchDone(int $limit = 0, int $offset = 0)
    {
        return $this->fetchWhere([QueueInterface::STATUS_DONE], $limit, $offset);
    }

    public function fetchFailed(int $limit = 0, int $offset = 0)
    {
        return $this->fetchWhere([QueueInterface::STATUS_FAILED], $limit, $offset);
    }

    public function markAs(JobInterface $job, int $status, string $message = '')
    {
        $job->setStatus($status);
        $job->setChanged(new DateTime());
        $job->setStatusMessage($message);
        $this->update($job);
        $this->persistenceManager->persistAll();
    }

    public function markAsPending(JobInterface $job)
    {
        $this->markAs($job, QueueInterface::STATUS_PENDING);
    }

    public function markAsRunning(JobInterface $job)
    {
        $this->markAs($job, QueueInterface::STATUS_RUNNING);
    }

    public function markAsDone(JobInterface $job)
    {
        $this->markAs($job, QueueInterface::STATUS_DONE);
    }

    public function markAsFailed(JobInterface $job, string $message = '')
    {
        $this->markAs($job, QueueInterface::STATUS_FAILED, $message);
    }

    public function markListAsRunning(array $jobs)
    {
        foreach ($jobs as $job) {
            $this->markAsRunning($job);
        }
    }

    public function markListAsDone(array $jobs)
    {
        foreach ($jobs as $job) {
            $this->markAsDone($job);
        }
    }

    public function markListAsFailed(array $jobs, string $message = '')
    {
        foreach ($jobs as $job) {
            $this->markAsFailed($job, $message);
        }
    }

    public function addJob(array $data, $status = QueueInterface::STATUS_PENDING): JobInterface
    {
        $repositoryConfig = $data['repository'] ?? [];
        unset($data['repository']);

        $job = new Job();
        if (isset($repositoryConfig['pid'])) {
            $job->setPid($repositoryConfig['pid']);
        }
        $job->setStatus($status);
        $job->setData($data);
        $this->add($job);
        $this->persistenceManager->persistAll();
        return $job;
    }

    public function removeJob(JobInterface $job)
    {
        $realJob = $this->findByUid($job->getId());
        if ($realJob) {
            $this->remove($realJob);
            $this->persistenceManager->persistAll();
        }
    }

    public function removeOldJobs(int $minAgeInSeconds, array $status = [])
    {
        $jobs = $this->fetchWhere($status, 0, 0, 0, $minAgeInSeconds);
        foreach ($jobs as $job) {
            $this->remove($job);
        }
        $this->persistenceManager->persistAll();
    }
}
