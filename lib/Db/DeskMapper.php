<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Desk>
 */
class DeskMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'deskbooking_desks', Desk::class);
    }

    /**
     * @param int $id
     * @return Desk
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     */
    public function find(int $id): Desk {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));

        return $this->findEntity($qb);
    }

    /**
     * @param string $name
     * @return Desk
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findByName(string $name): Desk {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('name', $qb->createNamedParameter($name)));

        return $this->findEntity($qb);
    }

    /**
     * @return Desk[]
     */
    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->orderBy('name', 'ASC');

        return $this->findEntities($qb);
    }

    /**
     * @return Desk[]
     */
    public function findActive(): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('is_active', $qb->createNamedParameter(true, IQueryBuilder::PARAM_BOOL)))
           ->orderBy('name', 'ASC');

        return $this->findEntities($qb);
    }
}
