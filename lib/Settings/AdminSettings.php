<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {

    public function getForm(): TemplateResponse {
        return new TemplateResponse('deskbooking', 'admin');
    }

    public function getSection(): string {
        return 'deskbooking';
    }

    public function getPriority(): int {
        return 50;
    }
}
