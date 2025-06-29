<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Service;

use DateInterval;
use DateTime;
use Exception;
use OCA\DeskBooking\Db\Booking;
use OCA\DeskBooking\Db\BookingMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IUserManager;

class BookingService {
    private BookingMapper $mapper;
    private IUserManager $userManager;

    public function __construct(BookingMapper $mapper, IUserManager $userManager) {
        $this->mapper = $mapper;
        $this->userManager = $userManager;
    }

    /**
     * @return Booking[]
     */
    public function findAll(): array {
        $bookings = $this->mapper->findAll();
        
        // Add display names using IUserManager
        foreach ($bookings as $booking) {
            $user = $this->userManager->get($booking->getUserId());
            if ($user !== null) {
                $displayName = $user->getDisplayName();
                $booking->setDisplayName($displayName);
            } else {
                // Fallback to user ID if user not found
                $booking->setDisplayName($booking->getUserId());
            }
        }
        
        return $bookings;
    }

    /**
     * @param int $id
     * @return Booking
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function find(int $id): Booking {
        return $this->mapper->find($id);
    }

    /**
     * @param string $userId
     * @return Booking[]
     */
    public function findByUser(string $userId): array {
        return $this->mapper->findByUser($userId);
    }

    /**
     * @param int $deskId
     * @return Booking[]
     */
    public function findByDesk(int $deskId): array {
        return $this->mapper->findByDesk($deskId);
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return Booking[]
     */
    public function findByDateRange(DateTime $startDate, DateTime $endDate): array {
        return $this->mapper->findByDateRange($startDate, $endDate);
    }

    /**
     * @param int $deskId
     * @param string $userId
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @param string $notes
     * @return Booking
     * @throws Exception
     */
    public function create(int $deskId, string $userId, DateTime $startTime, DateTime $endTime, string $notes = ''): Booking {
        // Validate time range
        if ($startTime >= $endTime) {
            throw new Exception('Start time must be before end time');
        }

        // Check for conflicts
        if ($this->mapper->hasConflictingBooking($deskId, $startTime, $endTime)) {
            throw new Exception('Desk is already booked for this time slot');
        }

        $booking = new Booking();
        $booking->setDeskId($deskId);
        $booking->setUserId($userId);
        $booking->setStartTime($startTime);
        $booking->setEndTime($endTime);
        $booking->setNotes($notes);
        $booking->setCreatedAt(new DateTime());
        $booking->setUpdatedAt(new DateTime());

        return $this->mapper->insert($booking);
    }

    /**
     * Create daily time slot bookings for multi-day reservations
     * @param int $deskId
     * @param string $userId
     * @param string $startDate
     * @param string $endDate
     * @param string $startTime
     * @param string $endTime
     * @param string $notes
     * @return Booking[]
     * @throws Exception
     */
    public function createDailySlots(int $deskId, string $userId, string $startDate, string $endDate, string $startTime, string $endTime, string $notes = ''): array {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        // Validate date range
        if ($start > $end) {
            throw new Exception('Start date must be before or equal to end date');
        }
        
        // Validate time range
        $testStart = new DateTime($startDate . 'T' . $startTime);
        $testEnd = new DateTime($startDate . 'T' . $endTime);
        if ($testStart >= $testEnd) {
            throw new Exception('Start time must be before end time');
        }
        
        $createdBookings = [];
        $current = clone $start;
        
        // Create booking for each day in the range
        while ($current <= $end) {
            $dayStart = new DateTime($current->format('Y-m-d') . 'T' . $startTime);
            $dayEnd = new DateTime($current->format('Y-m-d') . 'T' . $endTime);
            
            // Check for conflicts on this day
            if ($this->mapper->hasConflictingBooking($deskId, $dayStart, $dayEnd)) {
                throw new Exception('Desk is already booked on ' . $current->format('Y-m-d') . ' for the specified time slot');
            }
            
            // Create booking for this day
            $booking = new Booking();
            $booking->setDeskId($deskId);
            $booking->setUserId($userId);
            $booking->setStartTime($dayStart);
            $booking->setEndTime($dayEnd);
            $booking->setNotes($notes);
            $booking->setCreatedAt(new DateTime());
            $booking->setUpdatedAt(new DateTime());
            
            $createdBookings[] = $this->mapper->insert($booking);
            
            // Move to next day
            $current->add(new DateInterval('P1D'));
        }
        
        return $createdBookings;
    }

    /**
     * @param int $id
     * @param string $userId
     * @return Booking
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     * @throws Exception
     */
    public function update(int $id, string $userId, DateTime $startTime, DateTime $endTime, string $notes): Booking {
        $booking = $this->mapper->find($id);

        // Only allow users to edit their own bookings
        if ($booking->getUserId() !== $userId) {
            throw new Exception('You can only edit your own bookings');
        }

        // Validate time range
        if ($startTime >= $endTime) {
            throw new Exception('Start time must be before end time');
        }

        // Check for conflicts (excluding current booking)
        if ($this->mapper->hasConflictingBooking($booking->getDeskId(), $startTime, $endTime, $id)) {
            throw new Exception('Desk is already booked for this time slot');
        }

        $booking->setStartTime($startTime);
        $booking->setEndTime($endTime);
        $booking->setNotes($notes);
        $booking->setUpdatedAt(new DateTime());

        return $this->mapper->update($booking);
    }

    /**
     * @param int $id
     * @param string $userId
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     * @throws Exception
     */
    public function delete(int $id, string $userId): void {
        $booking = $this->mapper->find($id);

        // Only allow users to delete their own bookings
        if ($booking->getUserId() !== $userId) {
            throw new Exception('You can only delete your own bookings');
        }

        $this->mapper->delete($booking);
    }

    /**
     * @param int $id
     * @param string $userId
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     * @throws Exception
     */
    public function cancel(int $id, string $userId): void {
        // Cancel is the same as delete - remove the booking from database
        $this->delete($id, $userId);
    }
}
