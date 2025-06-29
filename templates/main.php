<?php
script(\OCA\DeskBooking\AppInfo\Application::APP_ID, 'main');
style(\OCA\DeskBooking\AppInfo\Application::APP_ID, 'style');
?>

<div id="deskbooking-app">
    <div class="app-content">
        <div class="app-content-header">
            <h2><?php p($l->t('Desk Booking System')); ?></h2>
            <div class="app-content-controls">
                <button id="view-toggle" class="secondary"><?php p($l->t('Calendar View')); ?></button>
                <button id="new-booking-btn" class="primary"><?php p($l->t('New Booking')); ?></button>
            </div>
        </div>
        <div id="booking-content">
            <div class="loading-content" style="text-align: center; padding: 50px;">
                <div class="icon icon-loading" style="font-size: 48px; margin-bottom: 20px;"></div>
                <h2><?php p($l->t('Loading desk bookings...')); ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- New Booking Modal -->
<div id="booking-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title"><?php p($l->t('New Booking')); ?></h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="booking-form">
                <div class="form-group">
                    <label for="desk-select"><?php p($l->t('Desk')); ?>:</label>
                    <select id="desk-select" name="deskId" required>
                        <option value=""><?php p($l->t('Select a desk...')); ?></option>
                    </select>
                </div>
                <div class="form-group form-row">
                    <div class="form-field">
                        <label for="start-date"><?php p($l->t('Start Date')); ?>:</label>
                        <input type="date" id="start-date" name="startDate" required title="<?php p($l->t('End date will automatically match start date for single-day bookings')); ?>">
                    </div>
                    <div class="form-field">
                        <label for="end-date"><?php p($l->t('End Date')); ?>:</label>
                        <input type="date" id="end-date" name="endDate" required>
                    </div>
                </div>
                <div class="form-group form-row">
                    <div class="form-field">
                        <label for="start-time"><?php p($l->t('Start Time')); ?>:</label>
                        <input type="time" id="start-time" name="startTime" required>
                    </div>
                    <div class="form-field">
                        <label for="end-time"><?php p($l->t('End Time')); ?>:</label>
                        <input type="time" id="end-time" name="endTime" required>
                    </div>
                </div>
                <div class="form-group">
                    <small class="form-hint"><?php p($l->t('For multi-day bookings, a separate reservation will be created for each day with the specified time slot')); ?></small>
                </div>
                <div class="form-group">
                    <label for="booking-notes"><?php p($l->t('Notes')); ?>:</label>
                    <textarea id="booking-notes" name="notes" rows="3" placeholder="<?php p($l->t('Optional notes')); ?>"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary modal-cancel"><?php p($l->t('Cancel')); ?></button>
            <button type="submit" form="booking-form" class="primary"><?php p($l->t('Save Booking')); ?></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Desk Booking App loaded with proper script loading');
    
    // Admin panel navigation
    const adminBtn = document.getElementById('admin-btn');
    const adminLinkBtn = document.getElementById('admin-link-btn');
    
    function goToAdmin() {
        window.location.href = OC.generateUrl('/apps/deskbooking/admin');
    }
    
    if (adminBtn) {
        adminBtn.addEventListener('click', goToAdmin);
    }
    
    if (adminLinkBtn) {
        adminLinkBtn.addEventListener('click', goToAdmin);
    }
    
    // Modal functionality
    const newBookingBtn = document.getElementById('new-booking-btn');
    const bookingModal = document.getElementById('booking-modal');
    const modalClose = document.querySelector('.modal-close');
    const modalCancel = document.querySelector('.modal-cancel');
    
    if (newBookingBtn && bookingModal) {
        newBookingBtn.addEventListener('click', function() {
            bookingModal.style.display = 'block';
        });
    }
    
    function closeModal() {
        if (bookingModal) {
            bookingModal.style.display = 'none';
        }
    }
    
    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }
    
    if (modalCancel) {
        modalCancel.addEventListener('click', closeModal);
    }
    
    // Close modal when clicking outside
    if (bookingModal) {
        bookingModal.addEventListener('click', function(e) {
            if (e.target === bookingModal) {
                closeModal();
            }
        });
    }
});
</script>
