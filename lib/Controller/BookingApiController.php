<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Controller;

use DateTime;
use Exception;
use OCA\DeskBooking\Service\BookingService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IRequest;
use OCP\IUserSession;

class BookingApiController extends Controller {
    private BookingService $service;
    private IUserSession $userSession;

    public function __construct(
        string $appName,
        IRequest $request,
        BookingService $service,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
        $this->userSession = $userSession;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): DataResponse {
        return new DataResponse($this->service->findAll());
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function show(int $id): DataResponse {
        try {
            return new DataResponse($this->service->find($id));
        } catch (DoesNotExistException $e) {
            return new DataResponse(['message' => 'Booking not found'], 404);
        } catch (Exception $e) {
            return new DataResponse(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function myBookings(): DataResponse {
        $userId = $this->userSession->getUser()->getUID();
        return new DataResponse($this->service->findByUser($userId));
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function byDesk(int $deskId): DataResponse {
        return new DataResponse($this->service->findByDesk($deskId));
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function byDateRange(string $startDate, string $endDate): DataResponse {
        try {
            $start = new DateTime($startDate);
            $end = new DateTime($endDate);
            return new DataResponse($this->service->findByDateRange($start, $end));
        } catch (Exception $e) {
            return new DataResponse(['message' => 'Invalid date format'], 400);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function create(
        int $deskId,
        string $startTime,
        string $endTime,
        string $startDate,
        string $endDate,
        string $bookingType = 'legacy',
        string $notes = ''
    ): DataResponse {
        try {
            $userId = $this->userSession->getUser()->getUID();
            
            // Handle new daily slots format
            if ($bookingType === 'daily_slots' && $startDate && $endDate && $startTime && $endTime) {
                return new DataResponse($this->service->createDailySlots($deskId, $userId, $startDate, $endDate, $startTime, $endTime, $notes));
            }
            
            // Handle legacy format for backward compatibility
            if ($startTime && $endTime) {
                $start = new DateTime($startTime);
                $end = new DateTime($endTime);
                return new DataResponse($this->service->create($deskId, $userId, $start, $end, $notes));
            }
            
            throw new Exception('Missing required booking parameters');
        } catch (Exception $e) {
            return new DataResponse(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function update(
        int $id,
        string $startTime,
        string $endTime,
        string $startDate,
        string $endDate,
        string $bookingType = 'legacy',
        string $notes = ''
    ): DataResponse {
        try {
            $userId = $this->userSession->getUser()->getUID();
            
            // Handle new daily slots format
            if ($bookingType === 'daily_slots' && $startDate && $endDate && $startTime && $endTime) {
                // For editing with daily slots, we need to delete the existing booking and create new ones
                // This is because we might be changing from single day to multi-day or vice versa
                try {
                    $booking = $this->service->find($id); // Get the original booking for desk info first
                    $deskId = $booking->getDeskId();
                    $this->service->delete($id, $userId);
                    return new DataResponse($this->service->createDailySlots($deskId, $userId, $startDate, $endDate, $startTime, $endTime, $notes));
                } catch (DoesNotExistException $e) {
                    return new DataResponse(['message' => 'Booking not found'], 404);
                }
            }
            
            // Handle legacy format for backward compatibility
            if ($startTime && $endTime) {
                try {
                    $start = new DateTime($startTime);
                    $end = new DateTime($endTime);
                    return new DataResponse($this->service->update($id, $userId, $start, $end, $notes));
                } catch (DoesNotExistException $e) {
                    return new DataResponse(['message' => 'Booking not found'], 404);
                }
            }
            
            throw new Exception('Missing required booking parameters');
        } catch (Exception $e) {
            return new DataResponse(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function destroy(int $id): DataResponse {
        try {
            $userId = $this->userSession->getUser()->getUID();
            $this->service->delete($id, $userId);
            return new DataResponse(['message' => 'Booking deleted successfully']);
        } catch (DoesNotExistException $e) {
            return new DataResponse(['message' => 'Booking not found'], 404);
        } catch (Exception $e) {
            return new DataResponse(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function cancel(int $id): DataResponse {
        try {
            $userId = $this->userSession->getUser()->getUID();
            $this->service->delete($id, $userId);
            return new DataResponse(['message' => 'Booking cancelled successfully']);
        } catch (DoesNotExistException $e) {
            return new DataResponse(['message' => 'Booking not found'], 404);
        } catch (Exception $e) {
            return new DataResponse(['message' => $e->getMessage()], 400);
        }
    }
}
