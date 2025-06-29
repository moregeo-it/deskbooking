<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Service;

use DateTime;
use Exception;
use OCA\DeskBooking\Db\Desk;
use OCA\DeskBooking\Db\DeskMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class DeskService {
    private DeskMapper $mapper;

    public function __construct(DeskMapper $mapper) {
        $this->mapper = $mapper;
    }

    /**
     * @return Desk[]
     */
    public function findAll(): array {
        return $this->mapper->findAll();
    }

    /**
     * @return Desk[]
     */
    public function findActive(): array {
        return $this->mapper->findActive();
    }

    /**
     * @param int $id
     * @return Desk
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function find(int $id): Desk {
        return $this->mapper->find($id);
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $location
     * @return Desk
     * @throws Exception
     */
    public function create(string $name, string $description = '', string $location = ''): Desk {
        $desk = new Desk();
        $desk->setName($name);
        $desk->setDescription($description);
        $desk->setLocation($location);
        $desk->setIsActive(true);
        $desk->setCreatedAt(new DateTime());
        $desk->setUpdatedAt(new DateTime());

        return $this->mapper->insert($desk);
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param string $location
     * @param bool $isActive
     * @return Desk
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function update(int $id, string $name, string $description, string $location, bool $isActive): Desk {
        $desk = $this->mapper->find($id);
        $desk->setName($name);
        $desk->setDescription($description);
        $desk->setLocation($location);
        $desk->setIsActive($isActive);
        $desk->setUpdatedAt(new DateTime());

        return $this->mapper->update($desk);
    }

    /**
     * @param int $id
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function delete(int $id): void {
        $desk = $this->mapper->find($id);
        $this->mapper->delete($desk);
    }
}
