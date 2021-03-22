<?php

namespace Mediatis\Formrelay\Domain\Model\Queue;

use DateTime;
use FormRelay\Core\Queue\JobInterface;
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

    /** @var string $statusMessage */
    protected $statusMessage;

    /** @var string $serializedData */
    protected $serializedData;

    /** @var string $route */
    protected $route;

    /** @var string $pass */
    protected $pass;

    /** @var string $label */
    protected $label;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->changed = new DateTime();
        $this->status = QueueInterface::STATUS_PENDING;
        $this->statusMessage = '';
        $this->serializedData = '';
    }

    public function updateMetaData()
    {
        $data = $this->getData();
        $this->setRoute($data['context']['job']['route'] ?? '');
        $this->setPass($data['context']['job']['pass'] ?? '');
        $this->setLabel($this->getRoute() . '(' . $this->getPass() . ')');
    }

    public function getId(): int
    {
        return $this->uid;
    }

    public function setId(int $id)
    {
        $this->uid = $id;
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

    public function getPass(): string
    {
        return $this->pass;
    }

    public function setPass(string $pass)
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
        return json_decode($data, true);
    }

    public function setData(array $data)
    {
        $this->setSerializedData(json_encode($data, JSON_PRETTY_PRINT));
    }
}
