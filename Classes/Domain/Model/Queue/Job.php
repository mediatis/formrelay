<?php

namespace Mediatis\Formrelay\Domain\Model\Queue;

use DateTime;
use FormRelay\Core\Model\Queue\JobInterface;
use FormRelay\Core\Queue\QueueInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Job extends AbstractEntity implements JobInterface
{
    /** @var DateTime $created */
    protected $created;

    /** @var DateTime $changed */
    protected $changed;

    /** @var int $status */
    protected $status;

    /** @var bool $skipped */
    protected $skipped;

    /** @var string $statusMessage */
    protected $statusMessage;

    /** @var string $serializedData */
    protected $serializedData;

    /** @var string $route */
    protected $route;

    /** @var int $pass */
    protected $pass;

    /** @var string $hash */
    protected $hash;

    /** @var string $label */
    protected $label;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->changed = new DateTime();
        $this->status = QueueInterface::STATUS_PENDING;
        $this->statusMessage = '';
        $this->serializedData = '';
        $this->route = '';
        $this->pass = 0;
        $this->label = '';
        $this->hash = '';
    }

    protected function updateMetaData()
    {
        $data = $this->getData();
        if (!empty($data)) {
            if (isset($data['route'])) {
                $this->setRoute($data['route']);
            }
            if (isset($data['pass'])) {
                $this->setPass($data['pass']);
            }
        }
    }

    public function getId(): int
    {
        return $this->uid;
    }

    public function setId(int $id)
    {
        $this->uid = $id;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash)
    {
        $this->hash = $hash;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRoute(string $route)
    {
        $this->route = $route;
    }

    public function getPass(): int
    {
        return $this->pass;
    }

    public function setPass(int $pass)
    {
        $this->pass = $pass;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created)
    {
        $this->created = $created;
    }

    public function getChanged(): DateTime
    {
        return $this->changed;
    }

    public function setChanged(DateTime $changed)
    {
        $this->changed = $changed;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getSkipped(): bool
    {
        return $this->skipped;
    }

    public function setSkipped(bool $skipped)
    {
        $this->skipped = $skipped;
    }

    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }

    public function setStatusMessage(string $statusMessage)
    {
        $this->statusMessage = $statusMessage;
    }

    public function getSerializedData(): string
    {
        return $this->serializedData;
    }

    public function setSerializedData(string $serializedData)
    {
        $this->serializedData = $serializedData;
        $this->updateMetaData();
    }

    public function getData(): array
    {
        $data = $this->getSerializedData();
        if (!$data) {
            return [];
        }
        $data = json_decode($data, true);
        if (!$data) {
            return [];
        }
        return $data;
    }

    public function setData(array $data)
    {
        $serializedData = json_encode($data);
        if ($serializedData === false) {
            $this->setStatus(QueueInterface::STATUS_FAILED);
            $this->setStatusMessage('data encoding failed [' . json_last_error() . ']: "' . json_last_error_msg() . '"');

            $serializedData = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
            if ($serializedData === false) {
                if (isset($data['submission']['configuration'])) {
                    // remove "configuration" since print_r is not able to print big data sets completely
                    // and "data" and "context" are much more important (and usually much smaller)
                    unset($data['submission']['configuration']);
                }
                $serializedData = print_r($data, true);
            }
        }
        $this->setSerializedData($serializedData);
    }
}
