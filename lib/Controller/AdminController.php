<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Controller;

use OCA\DeskBooking\Service\DeskService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class AdminController extends Controller {
    private DeskService $deskService;

    public function __construct(string $appName, IRequest $request, DeskService $deskService) {
        parent::__construct($appName, $request);
        $this->deskService = $deskService;
    }

    /**
     * @AdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse {
        $desks = $this->deskService->findAll();

        return new TemplateResponse('deskbooking', 'admin', [
            'desks' => $desks
        ]);
    }

    /**
     * @AdminRequired
     * @NoCSRFRequired
     */
    public function getDesks(): DataResponse {
        $desks = $this->deskService->findAll();
        return new DataResponse($desks);
    }
}
