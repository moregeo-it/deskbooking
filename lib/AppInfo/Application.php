<?php

declare(strict_types=1);

namespace OCA\DeskBooking\AppInfo;

use OCA\DeskBooking\Service\BookingService;
use OCA\DeskBooking\Db\BookingMapper;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IUserManager;

class Application extends App implements IBootstrap {
    public const APP_ID = 'deskbooking';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        // Register BookingService with IUserManager dependency
        $context->registerService(BookingService::class, function ($c) {
            return new BookingService(
                $c->get(BookingMapper::class),
                $c->get(IUserManager::class)
            );
        });
    }

    public function boot(IBootContext $context): void {
        // Boot logic if needed
    }
}
