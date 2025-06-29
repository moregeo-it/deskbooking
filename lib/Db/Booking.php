<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getDeskId()
 * @method void setDeskId(int $deskId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getDisplayName()
 * @method void setDisplayName(string $displayName)
 * @method \DateTime getStartTime()
 * @method void setStartTime(\DateTime $startTime)
 * @method \DateTime getEndTime()
 * @method void setEndTime(\DateTime $endTime)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method \DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 * @method \DateTime getUpdatedAt()
 * @method void setUpdatedAt(\DateTime $updatedAt)
 */
class Booking extends Entity implements JsonSerializable {
    protected $deskId;
    protected $userId = '';
    protected $displayName = '';
    protected $startTime;
    protected $endTime;
    protected $notes = '';
    protected $createdAt;
    protected $updatedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('deskId', 'integer');
        $this->addType('startTime', 'datetime');
        $this->addType('endTime', 'datetime');
        $this->addType('createdAt', 'datetime');
        $this->addType('updatedAt', 'datetime');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->getId(),
            'deskId' => $this->getDeskId(),
            'userId' => $this->getUserId(),
            'displayName' => $this->getDisplayName(),
            'startTime' => $this->getStartTime() ? $this->getStartTime()->format('Y-m-d\TH:i:s') : null,
            'endTime' => $this->getEndTime() ? $this->getEndTime()->format('Y-m-d\TH:i:s') : null,
            'notes' => $this->getNotes(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d\TH:i:s') : null,
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d\TH:i:s') : null,
        ];
    }
}
