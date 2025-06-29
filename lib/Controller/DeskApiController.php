<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Controller;

use Exception;
use OCA\DeskBooking\Service\DeskService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class DeskApiController extends Controller {
    private DeskService $service;

    public function __construct(string $appName, IRequest $request, DeskService $service) {
        parent::__construct($appName, $request);
        $this->service = $service;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): DataResponse {
        return new DataResponse($this->service->findActive());
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function show(int $id): DataResponse {
        try {
            return new DataResponse($this->service->find($id));
        } catch (Exception $e) {
            return new DataResponse(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function create(string $name, string $description = '', string $location = ''): DataResponse {
        try {
            return new DataResponse($this->service->create($name, $description, $location));
        } catch (Exception $e) {
            return new DataResponse(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function update(int $id, string $name, string $description, string $location, bool $isActive): DataResponse {
        try {
            return new DataResponse($this->service->update($id, $name, $description, $location, $isActive));
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
            $this->service->delete($id);
            return new DataResponse(['message' => 'Desk deleted successfully']);
        } catch (Exception $e) {
            return new DataResponse(['message' => $e->getMessage()], 400);
        }
    }
}
