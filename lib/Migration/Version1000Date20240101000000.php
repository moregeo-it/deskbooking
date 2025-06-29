<?php

declare(strict_types=1);

namespace OCA\DeskBooking\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20240101000000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // Create desks table
        if (!$schema->hasTable('deskbooking_desks')) {
            $table = $schema->createTable('deskbooking_desks');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('name', 'string', [
                'notnull' => true,
                'length' => 100,
            ]);
            $table->addColumn('description', 'text', [
                'notnull' => false,
            ]);
            $table->addColumn('location', 'string', [
                'notnull' => false,
                'length' => 200,
            ]);
            $table->addColumn('is_active', 'boolean', [
                'notnull' => false,
                'default' => true,
            ]);
            $table->addColumn('created_at', 'datetime', [
                'notnull' => true,
            ]);
            $table->addColumn('updated_at', 'datetime', [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['name'], 'desk_name_unique');
        }

        // Create bookings table
        if (!$schema->hasTable('deskbooking_bookings')) {
            $table = $schema->createTable('deskbooking_bookings');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('desk_id', 'integer', [
                'notnull' => true,
            ]);
            $table->addColumn('user_id', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('start_time', 'datetime', [
                'notnull' => true,
            ]);
            $table->addColumn('end_time', 'datetime', [
                'notnull' => true,
            ]);
            $table->addColumn('notes', 'text', [
                'notnull' => false,
            ]);
            $table->addColumn('created_at', 'datetime', [
                'notnull' => true,
            ]);
            $table->addColumn('updated_at', 'datetime', [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['desk_id'], 'booking_desk_id');
            $table->addIndex(['user_id'], 'booking_user_id');
            $table->addIndex(['start_time', 'end_time'], 'booking_time_range');
        }

        return $schema;
    }
}
