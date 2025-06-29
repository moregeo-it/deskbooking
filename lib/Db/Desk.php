<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method void setName(string $name)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method string getLocation()
 * @method void setLocation(string $location)
 * @method bool getIsActive()
 * @method void setIsActive(bool $isActive)
 * @method \DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 * @method \DateTime getUpdatedAt()
 * @method void setUpdatedAt(\DateTime $updatedAt)
 */
class Desk extends Entity implements JsonSerializable {
    protected $name = '';
    protected $description = '';
    protected $location = '';
    protected $isActive = true;
    protected $createdAt;
    protected $updatedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('isActive', 'boolean');
        $this->addType('createdAt', 'datetime');
        $this->addType('updatedAt', 'datetime');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'location' => $this->getLocation(),
            'isActive' => $this->getIsActive(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];
    }
}
