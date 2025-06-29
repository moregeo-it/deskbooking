<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller {
    public function __construct(string $appName, IRequest $request) {
        parent::__construct($appName, $request);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse {
        $id = \OCA\DeskBooking\AppInfo\Application::APP_ID;
        Util::addScript($id, 'main');
        Util::addStyle($id, 'style');
        return new TemplateResponse($id, 'main');
    }
}
