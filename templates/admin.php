<?php
script(\OCA\DeskBooking\AppInfo\Application::APP_ID, 'admin');
style(\OCA\DeskBooking\AppInfo\Application::APP_ID, 'admin');
?>

<div id="admin-content" class="section">
    <div style="margin-bottom: 20px;">
        <a href="<?php p(\OC::$server->getURLGenerator()->linkToRoute('deskbooking.page.index')); ?>" class="button">
            <span class="icon icon-home"></span> <?php p($l->t('Back to Desk Booking')); ?>
        </a>
    </div>
    
    <h2><?php p($l->t('Desk Booking Administration')); ?></h2>
    
    <div class="admin-section">
        <h3><?php p($l->t('Manage Desks')); ?></h3>
        <div class="admin-controls">
            <button id="add-desk-btn" class="primary"><?php p($l->t('Add New Desk')); ?></button>
        </div>
        
        <table id="desks-table" class="admin-table">
            <thead>
                <tr>
                    <th><?php p($l->t('Name')); ?></th>
                    <th><?php p($l->t('Description')); ?></th>
                    <th><?php p($l->t('Location')); ?></th>
                    <th><?php p($l->t('Status')); ?></th>
                    <th><?php p($l->t('Actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_['desks'] ?? [] as $desk): ?>
                <tr data-desk-id="<?php p($desk->getId()); ?>">
                    <td><?php p($desk->getName()); ?></td>
                    <td><?php p($desk->getDescription()); ?></td>
                    <td><?php p($desk->getLocation()); ?></td>
                    <td class="desk-status <?php p($desk->getIsActive() ? 'active' : 'inactive'); ?>">
                        <?php p($desk->getIsActive() ? $l->t('Active') : $l->t('Inactive')); ?>
                    </td>
                    <td>
                        <button class="edit-desk-btn" data-desk-id="<?php p($desk->getId()); ?>"><?php p($l->t('Edit')); ?></button>
                        <button class="delete-desk-btn" data-desk-id="<?php p($desk->getId()); ?>"><?php p($l->t('Delete')); ?></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Desk Modal -->
<div id="desk-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="desk-modal-title"><?php p($l->t('Add New Desk')); ?></h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="desk-form">
                <input type="hidden" id="desk-id" name="deskId">
                <div class="form-group">
                    <label for="desk-name"><?php p($l->t('Name')); ?>:</label>
                    <input type="text" id="desk-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="desk-description"><?php p($l->t('Description')); ?>:</label>
                    <textarea id="desk-description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="desk-location"><?php p($l->t('Location')); ?>:</label>
                    <input type="text" id="desk-location" name="location">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="desk-active" name="isActive" checked>
                        <?php p($l->t('Active')); ?>
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary modal-cancel"><?php p($l->t('Cancel')); ?></button>
            <button type="submit" form="desk-form" class="primary"><?php p($l->t('Save Desk')); ?></button>
        </div>
    </div>
</div>
