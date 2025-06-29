<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Db;

use DateTime;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Booking>
 */
class BookingMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'deskbooking_bookings', Booking::class);
    }

    /**
     * @param int $id
     * @return Booking
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function find(int $id): Booking {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));

        return $this->findEntity($qb);
    }

    /**
     * @param string $userId
     * @return Booking[]
     */
    public function findByUser(string $userId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
           ->orderBy('start_time', 'ASC');

        return $this->findEntities($qb);
    }

    /**
     * @param int $deskId
     * @return Booking[]
     */
    public function findByDesk(int $deskId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('desk_id', $qb->createNamedParameter($deskId)))
           ->orderBy('start_time', 'ASC');

        return $this->findEntities($qb);
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return Booking[]
     */
    public function findByDateRange(DateTime $startDate, DateTime $endDate): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where(
               $qb->expr()->andX(
                   $qb->expr()->lt('start_time', $qb->createNamedParameter($endDate, IQueryBuilder::PARAM_DATE)),
                   $qb->expr()->gt('end_time', $qb->createNamedParameter($startDate, IQueryBuilder::PARAM_DATE))
               )
           )
           ->orderBy('start_time', 'ASC');

        return $this->findEntities($qb);
    }

    /**
     * @param int $deskId
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @param int|null $excludeBookingId
     * @return bool
     */
    public function hasConflictingBooking(int $deskId, DateTime $startTime, DateTime $endTime, ?int $excludeBookingId = null): bool {
        $qb = $this->db->getQueryBuilder();

        $qb->select('id')
           ->from($this->getTableName())
           ->where(
               $qb->expr()->andX(
                   $qb->expr()->eq('desk_id', $qb->createNamedParameter($deskId)),
                   $qb->expr()->orX(
                       // New booking starts during existing booking
                       $qb->expr()->andX(
                           $qb->expr()->lte('start_time', $qb->createNamedParameter($startTime, IQueryBuilder::PARAM_DATE)),
                           $qb->expr()->gt('end_time', $qb->createNamedParameter($startTime, IQueryBuilder::PARAM_DATE))
                       ),
                       // New booking ends during existing booking
                       $qb->expr()->andX(
                           $qb->expr()->lt('start_time', $qb->createNamedParameter($endTime, IQueryBuilder::PARAM_DATE)),
                           $qb->expr()->gte('end_time', $qb->createNamedParameter($endTime, IQueryBuilder::PARAM_DATE))
                       ),
                       // New booking encompasses existing booking
                       $qb->expr()->andX(
                           $qb->expr()->gte('start_time', $qb->createNamedParameter($startTime, IQueryBuilder::PARAM_DATE)),
                           $qb->expr()->lte('end_time', $qb->createNamedParameter($endTime, IQueryBuilder::PARAM_DATE))
                       )
                   )
               )
           );

        if ($excludeBookingId !== null) {
            $qb->andWhere($qb->expr()->neq('id', $qb->createNamedParameter($excludeBookingId)));
        }

        $qb->setMaxResults(1);

        return count($this->findEntities($qb)) > 0;
    }

    /**
     * @return Booking[]
     */
    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->orderBy('start_time', 'ASC');

        return $this->findEntities($qb);
    }
}
