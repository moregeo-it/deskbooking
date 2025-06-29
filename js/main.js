/**
 * Desk Booking App - Main JavaScript
 */

class DeskBookingApp {
    constructor() {
        this.currentView = 'grid';
        this.currentDate = new Date();
        this.desks = [];
        this.bookings = [];
        this.currentUser = null;
        
        this.init();
    }

    async init() {
        await this.loadInitialData();
        this.setupEventListeners();
        this.renderView();
    }

    async loadInitialData() {
        try {
            await Promise.all([
                this.loadDesks(),
                this.loadBookings()
            ]);
        } catch (error) {
            console.error('Failed to load initial data:', error);
            this.showError('Failed to load desk booking data');
        }
    }

    async loadDesks() {
        try {
            const response = await fetch(OC.generateUrl('/apps/deskbooking/api/desks'), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'requesttoken': OC.requestToken
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to load desks');
            }
            
            let desksData;
            try {
                desksData = await response.json();
            } catch (jsonError) {
                throw new Error('Invalid response format when loading desks');
            }
            
            this.desks = desksData;
            this.populateDeskSelect();
        } catch (error) {
            console.error('Error loading desks:', error);
            this.showError(error.message || 'Failed to load desks');
            throw error;
        }
    }

    async loadBookings() {
        try {
            const response = await fetch(OC.generateUrl('/apps/deskbooking/api/bookings'), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'requesttoken': OC.requestToken
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to load bookings');
            }
            
            let bookingsData;
            try {
                bookingsData = await response.json();
            } catch (jsonError) {
                throw new Error('Invalid response format when loading bookings');
            }
            
            this.bookings = bookingsData;
            this.renderView();
        } catch (error) {
            console.error('Error loading bookings:', error);
            this.showError(error.message || 'Failed to load bookings');
        }
    }

    setupEventListeners() {
        // New booking button
        document.getElementById('new-booking-btn').addEventListener('click', () => {
            this.openBookingModal();
        });

        // View toggle button
        document.getElementById('view-toggle').addEventListener('click', () => {
            this.toggleView();
        });

        // Modal close events
        document.querySelectorAll('.modal-close, .modal-cancel').forEach(element => {
            element.addEventListener('click', () => {
                this.closeModals();
            });
        });

        // Modal click outside to close
        document.getElementById('booking-modal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                this.closeModals();
            }
        });

        // Booking form submission
        document.getElementById('booking-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleBookingSubmit();
        });

        // Date/time validation
        document.getElementById('start-date').addEventListener('change', () => {
            this.validateDates();
            // Auto-update end date to match start date for new bookings (single-day default)
            this.updateEndDateForNewBooking();
        });
        
        document.getElementById('end-date').addEventListener('change', () => {
            this.validateDates();
        });
        
        document.getElementById('start-time').addEventListener('change', () => {
            this.validateDates();
        });
        
        document.getElementById('end-time').addEventListener('change', () => {
            this.validateDates();
        });
    }

    populateDeskSelect() {
        const select = document.getElementById('desk-select');
        select.innerHTML = '<option value="">Select a desk...</option>';
        
        this.desks.forEach(desk => {
            if (desk.isActive) {
                const option = document.createElement('option');
                option.value = desk.id;
                option.textContent = `${desk.name}${desk.location ? ' - ' + desk.location : ''}`;
                select.appendChild(option);
            }
        });
        
        // Preselect the last used desk for new bookings
        const lastDeskId = this.getLastSelectedDesk();
        if (lastDeskId && !select.disabled) {
            select.value = lastDeskId;
        }
    }

    getLastSelectedDesk() {
        try {
            return localStorage.getItem('deskbooking_last_desk_id');
        } catch (error) {
            console.warn('Could not retrieve last selected desk:', error);
            return null;
        }
    }

    saveLastSelectedDesk(deskId) {
        try {
            localStorage.setItem('deskbooking_last_desk_id', deskId.toString());
        } catch (error) {
            console.warn('Could not save last selected desk:', error);
        }
    }

    openBookingModal(booking = null) {
        const modal = document.getElementById('booking-modal');
        const form = document.getElementById('booking-form');
        const title = document.getElementById('modal-title');
        const modalFooter = modal.querySelector('.modal-footer');

        if (booking) {
            title.textContent = 'Edit Booking';
            this.populateBookingForm(booking);
            form.dataset.bookingId = booking.id;
            form.dataset.isEdit = 'true';
            
            // Remove existing delete button if it exists
            const existingDeleteBtn = modalFooter.querySelector('.delete-booking-btn');
            if (existingDeleteBtn) {
                existingDeleteBtn.remove();
            }
            
            // Add delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'delete-booking-btn';
            deleteBtn.textContent = 'Delete Booking';
            deleteBtn.style.backgroundColor = '#f44336';
            deleteBtn.style.color = 'white';
            deleteBtn.style.border = 'none';
            deleteBtn.style.padding = '8px 16px';
            deleteBtn.style.borderRadius = '4px';
            deleteBtn.style.cursor = 'pointer';
            deleteBtn.style.fontSize = '14px';
            deleteBtn.style.marginRight = 'auto';
            
            deleteBtn.addEventListener('click', () => {
                // Get the booking ID from the form's dataset to ensure we have the current booking
                const currentBookingId = form.dataset.bookingId;
                if (currentBookingId) {
                    this.deleteBooking(parseInt(currentBookingId));
                }
            });
            
            modalFooter.insertBefore(deleteBtn, modalFooter.firstChild);
        } else {
            title.textContent = 'New Booking';
            form.reset();
            delete form.dataset.bookingId;
            delete form.dataset.isEdit;
            
            // Remove delete button if exists
            const deleteBtn = modalFooter.querySelector('.delete-booking-btn');
            if (deleteBtn) {
                deleteBtn.remove();
            }
            
            // Re-enable desk selection for new bookings
            document.getElementById('desk-select').disabled = false;
            
            // Set default dates to tomorrow (next day)
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            document.getElementById('start-date').value = tomorrow.toISOString().split('T')[0];
            document.getElementById('end-date').value = tomorrow.toISOString().split('T')[0];
            document.getElementById('start-time').value = '09:00';
            document.getElementById('end-time').value = '17:00';
            
            // Preselect the last used desk
            const lastDeskId = this.getLastSelectedDesk();
            if (lastDeskId) {
                document.getElementById('desk-select').value = lastDeskId;
            }
        }

        modal.style.display = 'flex';
    }

    populateBookingForm(booking) {
        const startDateTime = new Date(booking.startTime);
        const endDateTime = new Date(booking.endTime);

        document.getElementById('desk-select').value = booking.deskId;
        document.getElementById('start-date').value = startDateTime.toISOString().split('T')[0];
        document.getElementById('start-time').value = startDateTime.toTimeString().substr(0, 5);
        document.getElementById('end-date').value = endDateTime.toISOString().split('T')[0];
        document.getElementById('end-time').value = endDateTime.toTimeString().substr(0, 5);
        document.getElementById('booking-notes').value = booking.notes || '';
        
        // Disable desk selection when editing
        document.getElementById('desk-select').disabled = true;
    }

    closeModals() {
        document.getElementById('booking-modal').style.display = 'none';
    }

    validateDates() {
        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;
        const startTime = document.getElementById('start-time').value;
        const endTime = document.getElementById('end-time').value;

        if (startDate && endDate) {
            const start = new Date(startDate + 'T' + (startTime || '00:00'));
            const end = new Date(endDate + 'T' + (endTime || '23:59'));

            // For same day bookings, check that end time is after start time
            // For different days, end date just needs to be >= start date
            if (startDate === endDate) {
                // Same day booking - end time must be after start time
                if (startTime && endTime && startTime >= endTime) {
                    document.getElementById('end-time').setCustomValidity('End time must be after start time on the same day');
                } else {
                    document.getElementById('end-time').setCustomValidity('');
                }
                document.getElementById('end-date').setCustomValidity('');
            } else {
                // Different days - end date must be after start date
                if (start >= end) {
                    document.getElementById('end-date').setCustomValidity('End date must be after start date');
                } else {
                    document.getElementById('end-date').setCustomValidity('');
                }
                document.getElementById('end-time').setCustomValidity('');
            }
        }
    }

    updateEndDateForNewBooking() {
        const form = document.getElementById('booking-form');
        const isEdit = form.dataset.isEdit === 'true';
        
        // Only auto-update for new bookings, not when editing existing ones
        if (!isEdit) {
            const startDateInput = document.getElementById('start-date');
            const endDateInput = document.getElementById('end-date');
            
            if (startDateInput.value) {
                // Set end date to match start date (single-day booking by default)
                endDateInput.value = startDateInput.value;
                this.validateDates(); // Revalidate after updating
            }
        }
    }

    async handleBookingSubmit() {
        const form = document.getElementById('booking-form');
        const formData = new FormData(form);
        const isEdit = form.dataset.isEdit === 'true';
        const bookingId = form.dataset.bookingId;

        const startDate = formData.get('startDate');
        const endDate = formData.get('endDate');
        const startTime = formData.get('startTime');
        const endTime = formData.get('endTime');
        const notes = formData.get('notes');

        // For multi-day bookings, use daily time slots
        const bookingData = {
            startDate: startDate,
            endDate: endDate,
            startTime: startTime,
            endTime: endTime,
            notes: notes,
            bookingType: 'daily_slots'
        };

        // Only include deskId for new bookings
        if (!isEdit) {
            bookingData.deskId = parseInt(formData.get('deskId'));
            // Save the selected desk as the last used desk
            this.saveLastSelectedDesk(bookingData.deskId);
        }

        try {
            let response;
            if (isEdit && bookingId) {
                // Update existing booking
                response = await fetch(OC.generateUrl(`/apps/deskbooking/api/bookings/${bookingId}`), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'requesttoken': OC.requestToken
                    },
                    body: JSON.stringify(bookingData)
                });
            } else {
                // Create new booking
                response = await fetch(OC.generateUrl('/apps/deskbooking/api/bookings'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'requesttoken': OC.requestToken
                    },
                    body: JSON.stringify(bookingData)
                });
            }

            let result;
            const responseText = await response.text();
            
            try {
                result = JSON.parse(responseText);
            } catch (jsonError) {
                throw new Error(`Server error: ${responseText || response.statusText || 'Unknown error'}`);
            }

            if (!response.ok) {
                throw new Error(result.message || result.error || `Failed to ${isEdit ? 'update' : 'create'} booking`);
            }

            this.showSuccess(`Booking ${isEdit ? 'updated' : 'created'} successfully!`);
            this.closeModals();
            await this.loadBookings();

        } catch (error) {
            console.error(`Error ${isEdit ? 'updating' : 'creating'} booking:`, error);
            this.showError(error.message || 'An unexpected error occurred');
        }
    }

    async cancelBooking(bookingId) {
        if (!confirm('Are you sure you want to cancel this booking?')) {
            return;
        }

        try {
            const response = await fetch(OC.generateUrl(`/apps/deskbooking/api/bookings/${bookingId}/cancel`), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'requesttoken': OC.requestToken
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to cancel booking');
            }

            this.showSuccess('Booking cancelled successfully!');
            await this.loadBookings();

        } catch (error) {
            console.error('Error cancelling booking:', error);
            this.showError(error.message);
        }
    }

    async deleteBooking(bookingId) {
        if (!confirm('Are you sure you want to permanently delete this booking? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch(OC.generateUrl(`/apps/deskbooking/api/bookings/${bookingId}`), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'requesttoken': OC.requestToken
                }
            });

            let result;
            const responseText = await response.text();
            
            try {
                result = JSON.parse(responseText);
            } catch (jsonError) {
                throw new Error(`Server error: ${responseText || response.statusText || 'Unknown error'}`);
            }

            if (!response.ok) {
                throw new Error(result.message || result.error || 'Failed to delete booking');
            }

            this.showSuccess('Booking deleted successfully!');
            this.closeModals();
            await this.loadBookings();

        } catch (error) {
            console.error('Error deleting booking:', error);
            this.showError(error.message || 'An unexpected error occurred');
        }
    }

    async editBooking(bookingId) {
        try {
            const booking = this.bookings.find(b => b.id === bookingId);
            if (!booking) {
                throw new Error('Booking not found');
            }

            // Check if user owns this booking
            if (booking.userId !== OC.getCurrentUser().uid) {
                throw new Error('You can only edit your own bookings');
            }

            this.openBookingModal(booking);
        } catch (error) {
            console.error('Error opening booking for edit:', error);
            this.showError(error.message);
        }
    }

    toggleView() {
        const button = document.getElementById('view-toggle');
        
        if (this.currentView === 'grid') {
            this.currentView = 'calendar';
            button.textContent = 'List View';
        } else {
            this.currentView = 'grid';
            button.textContent = 'Calendar View';
        }
        
        this.renderView();
    }

    renderView() {
        const content = document.getElementById('booking-content');
        
        if (this.currentView === 'grid') {
            this.renderGridView(content);
        } else {
            this.renderCalendarView(content);
        }
    }

    renderGridView(container) {
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Start of today
        const nextWeek = new Date(today);
        nextWeek.setDate(today.getDate() + 7);
        nextWeek.setHours(23, 59, 59, 999); // End of next week

        const relevantBookings = this.bookings.filter(booking => {
            const bookingStart = new Date(booking.startTime);
            const bookingEnd = new Date(booking.endTime);
            // Show bookings that overlap with the time period (start before next week ends and end after today starts)
            return bookingStart < nextWeek && bookingEnd > today;
        });

        container.innerHTML = `
            <div class="booking-grid">
                ${this.desks.filter(desk => desk.isActive).map(desk => this.renderDeskCard(desk, relevantBookings)).join('')}
            </div>
        `;

        // Add event listeners for editable bookings
        container.querySelectorAll('.editable-booking').forEach(booking => {
            booking.addEventListener('click', (e) => {
                const bookingId = parseInt(e.currentTarget.dataset.bookingId);
                this.editBooking(bookingId);
            });
            
            booking.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const bookingId = parseInt(e.currentTarget.dataset.bookingId);
                    this.editBooking(bookingId);
                }
            });
        });
    }

    renderDeskCard(desk, bookings) {
        const deskBookings = bookings.filter(booking => booking.deskId === desk.id);
        const deskColor = this.getDeskColor(desk.id);
        
        return `
            <div class="desk-card" style="border-left: 4px solid ${deskColor};">
                <h3 style="color: ${deskColor};">${desk.name}</h3>
                <div class="desk-info">
                    ${desk.location ? `<div>Location: ${desk.location}</div>` : ''}
                    ${desk.description ? `<div>${desk.description}</div>` : ''}
                </div>
                <div class="desk-bookings">
                    ${deskBookings.length > 0 
                        ? deskBookings.map(booking => this.renderBookingSlot(booking, deskColor)).join('')
                        : '<div class="booking-slot available">Available this week</div>'
                    }
                </div>
            </div>
        `;
    }

    renderBookingSlot(booking, deskColor = null) {
        const startTime = new Date(booking.startTime);
        const endTime = new Date(booking.endTime);
        const isMyBooking = booking.userId === OC.getCurrentUser().uid;
        const color = deskColor || this.getDeskColor(booking.deskId);
        
        const formatDate = (date) => {
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        };

        return `
            <div class="booking-slot ${isMyBooking ? 'my-booking editable-booking' : 'booked'}" 
                 style="background-color: ${color}; border-color: ${color};"
                 ${isMyBooking ? `data-booking-id="${booking.id}" role="button" tabindex="0" title="Click to edit this booking"` : ''}>
                <div class="booking-details">
                    <div class="booking-time">
                        ${formatDate(startTime)} - ${formatDate(endTime)}
                    </div>
                    <div class="booking-user">${booking.displayName || booking.userId}</div>
                    ${booking.notes ? `<div class="booking-notes">${booking.notes}</div>` : ''}
                </div>
            </div>
        `;
    }

    renderCalendarView(container) {
        // Desk-based calendar implementation for 3-day view
        container.innerHTML = `
            <div class="calendar-view">
                <div class="calendar-header">
                    <h3>3-Day Desk View</h3>
                    <div class="calendar-nav">
                        <button id="prev-days-btn">&lt; Previous 3 Days</button>
                        <span id="current-days"></span>
                        <button id="next-days-btn">Next 3 Days &gt;</button>
                    </div>
                </div>
                <div class="calendar-table-container">
                    <table id="calendar-table" class="calendar-table">
                        <!-- Calendar table will be populated here -->
                    </table>
                </div>
            </div>
        `;

        // Add event listeners for navigation
        document.getElementById('prev-days-btn').addEventListener('click', () => {
            this.previousDays();
        });
        
        document.getElementById('next-days-btn').addEventListener('click', () => {
            this.nextDays();
        });

        this.updateCalendar();
    }

    updateCalendar() {
        const table = document.getElementById('calendar-table');
        const daysSpan = document.getElementById('current-days');
        
        if (!table) {
            console.error('Calendar table element not found');
            return;
        }
        
        if (!daysSpan) {
            console.error('Current days span element not found');
            return;
        }

        try {
            // Get 3 days starting from current date
            const days = [];
            for (let i = 0; i < 3; i++) {
                const day = new Date(this.currentDate);
                day.setDate(this.currentDate.getDate() + i);
                days.push(day);
            }

            // Update date range display
            const startDate = days[0].toLocaleDateString();
            const endDate = days[2].toLocaleDateString();
            daysSpan.textContent = `${startDate} - ${endDate}`;

            // Get active desks
            const activeDesks = this.desks.filter(desk => desk.isActive);
            
            // Generate table structure
            let html = '<thead><tr><th class="time-header">Day</th>';
            
            // Create day separator headers
            days.forEach(day => {
                const dayLabel = day.toLocaleDateString('en', {weekday: 'short', month: 'short', day: 'numeric'});
                const isToday = day.toDateString() === new Date().toDateString();
                html += `<th colspan="${activeDesks.length}" class="day-separator ${isToday ? 'today' : ''}">${dayLabel}</th>`;
            });
            html += '</tr><tr><th class="time-header">Desk</th>';
            
            // Desk headers for each day
            days.forEach(() => {
                activeDesks.forEach(desk => {
                    html += `<th class="desk-header">
                                <div class="desk-name">${desk.name}</div>
                                <div class="desk-info">${desk.location || ''}</div>
                             </th>`;
                });
            });
            html += '</tr></thead><tbody>';

            // Time slots for business hours (6 AM to 10 PM)
            const timeSlots = [];
            for (let hour = 6; hour <= 22; hour++) {
                timeSlots.push(hour);
            }

            // Pre-calculate all bookings and their positions
            const bookingMatrix = this.calculateBookingMatrix(days, activeDesks, timeSlots);

            timeSlots.forEach((hour, hourIndex) => {
                const timeDisplay = hour < 12 ? `${hour}:00` : 
                                  hour === 12 ? '12:00' : 
                                  `${hour - 12}:00`;
                html += `<tr><td class="time-cell">${timeDisplay}</td>`;
                
                // For each day and desk combination
                days.forEach((day, dayIndex) => {
                    activeDesks.forEach((desk, deskIndex) => {
                        const cellKey = `${dayIndex}-${deskIndex}-${hourIndex}`;
                        const cellData = bookingMatrix[cellKey];
                        
                        if (cellData && cellData.render) {
                            const booking = cellData.booking;
                            const rowspan = cellData.rowspan;
                            const isMyBooking = booking.userId === OC.getCurrentUser().uid;
                            const deskColor = this.getDeskColor(desk.id);
                            const isCurrentHour = new Date().getHours() === hour && 
                                                 day.toDateString() === new Date().toDateString();
                            
                            html += `<td class="calendar-cell ${isCurrentHour ? 'current-hour' : ''}" rowspan="${rowspan}">
                                        <div class="calendar-booking ${isMyBooking ? 'my-booking editable-booking' : 'other-booking'}" 
                                             style="background-color: ${deskColor}; border-color: ${deskColor};"
                                             ${isMyBooking ? `data-booking-id="${booking.id}" role="button" tabindex="0" title="Click to edit this booking"` : `title="${booking.displayName || booking.userId}${booking.notes ? ': ' + booking.notes : ''}"`}>
                                            <div class="booking-user">${booking.displayName || booking.userId}</div>
                                            <div class="booking-time">${this.formatTimeRange(booking.startTime, booking.endTime)}</div>
                                            ${booking.notes ? `<div class="booking-notes">${booking.notes}</div>` : ''}
                                        </div>
                                     </td>`;
                        } else if (cellData && !cellData.render) {
                            // This cell is covered by a rowspan from above, don't render anything
                        } else {
                            // Empty cell
                            const isCurrentHour = new Date().getHours() === hour && 
                                                 day.toDateString() === new Date().toDateString();
                            html += `<td class="calendar-cell ${isCurrentHour ? 'current-hour' : ''}"></td>`;
                        }
                    });
                });
                
                html += '</tr>';
            });

            html += '</tbody>';
            table.innerHTML = html;
            
            // Add event listeners for editable calendar bookings
            table.querySelectorAll('.editable-booking').forEach(booking => {
                booking.addEventListener('click', (e) => {
                    const bookingId = parseInt(e.currentTarget.dataset.bookingId);
                    this.editBooking(bookingId);
                });
                
                booking.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        const bookingId = parseInt(e.currentTarget.dataset.bookingId);
                        this.editBooking(bookingId);
                    }
                });
            });
            
        } catch (error) {
            console.error('Error updating calendar:', error);
        }
    }

    calculateBookingMatrix(days, activeDesks, timeSlots) {
        const matrix = {};
        
        days.forEach((day, dayIndex) => {
            activeDesks.forEach((desk, deskIndex) => {
                // Get all bookings for this desk on this day
                const dayBookings = this.getBookingsForDeskAndDay(desk.id, day);
                
                timeSlots.forEach((hour, hourIndex) => {
                    const cellKey = `${dayIndex}-${deskIndex}-${hourIndex}`;
                    const cellDate = new Date(day);
                    cellDate.setHours(hour, 0, 0, 0);
                    
                    // Find booking that should be displayed in this cell
                    const booking = this.findBookingForTimeSlot(dayBookings, cellDate);
                    
                    if (booking) {
                        // Check if this is the first hour of the booking display
                        const bookingStartHour = new Date(booking.startTime).getHours();
                        const effectiveStartHour = Math.max(bookingStartHour, 6); // Our display starts at 6 AM
                        
                        if (hour >= effectiveStartHour && !this.isBookingAlreadyRendered(matrix, dayIndex, deskIndex, booking, hourIndex)) {
                            // Calculate rowspan
                            const rowspan = this.calculateRowspanForBooking(booking, hour, timeSlots);
                            matrix[cellKey] = {
                                booking: booking,
                                rowspan: rowspan,
                                render: true
                            };
                            
                            // Mark subsequent cells as covered
                            for (let i = 1; i < rowspan; i++) {
                                const nextHourIndex = hourIndex + i;
                                if (nextHourIndex < timeSlots.length) {
                                    const nextCellKey = `${dayIndex}-${deskIndex}-${nextHourIndex}`;
                                    matrix[nextCellKey] = {
                                        booking: booking,
                                        rowspan: 0,
                                        render: false
                                    };
                                }
                            }
                        } else if (this.isBookingAlreadyRendered(matrix, dayIndex, deskIndex, booking, hourIndex)) {
                            // This cell is covered by a booking rendered above
                            matrix[cellKey] = {
                                booking: booking,
                                rowspan: 0,
                                render: false
                            };
                        }
                    }
                });
            });
        });
        
        return matrix;
    }

    isBookingAlreadyRendered(matrix, dayIndex, deskIndex, booking, hourIndex) {
        // Check previous hours to see if this booking is already rendered
        for (let i = hourIndex - 1; i >= 0; i--) {
            const prevCellKey = `${dayIndex}-${deskIndex}-${i}`;
            const prevCell = matrix[prevCellKey];
            if (prevCell && prevCell.booking.id === booking.id && prevCell.render) {
                return true;
            }
        }
        return false;
    }

    getBookingsForDeskAndDay(deskId, day) {
        const dayStart = new Date(day);
        dayStart.setHours(0, 0, 0, 0);
        const dayEnd = new Date(day);
        dayEnd.setHours(23, 59, 59, 999);

        return this.bookings.filter(booking => {
            const bookingStart = new Date(booking.startTime);
            const bookingEnd = new Date(booking.endTime);
            
            return booking.deskId === deskId &&
                   bookingStart < dayEnd && 
                   bookingEnd > dayStart;
        });
    }

    findBookingForTimeSlot(dayBookings, slotDate) {
        const slotEnd = new Date(slotDate);
        slotEnd.setHours(slotDate.getHours() + 1);

        return dayBookings.find(booking => {
            const bookingStart = new Date(booking.startTime);
            const bookingEnd = new Date(booking.endTime);
            
            return bookingStart < slotEnd && bookingEnd > slotDate;
        });
    }

    calculateRowspanForBooking(booking, startDisplayHour, timeSlots) {
        const bookingStart = new Date(booking.startTime);
        const bookingEnd = new Date(booking.endTime);
        
        // Find the effective start and end hours within our display range
        const displayStartHour = Math.max(startDisplayHour, 6);
        const displayEndHour = Math.min(Math.ceil(bookingEnd.getHours() + bookingEnd.getMinutes() / 60), 22);
        
        // Calculate how many rows this booking should span
        const span = Math.max(1, displayEndHour - startDisplayHour);
        
        // Make sure we don't exceed our time slots
        const maxSpan = timeSlots.length - timeSlots.indexOf(startDisplayHour);
        
        return Math.min(span, maxSpan);
    }

    formatTimeRange(startTime, endTime) {
        const start = new Date(startTime);
        const end = new Date(endTime);
        
        const formatTime = (date) => {
            return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        };
        
        return `${formatTime(start)}-${formatTime(end)}`;
    }

    previousDays() {
        try {
            this.currentDate.setDate(this.currentDate.getDate() - 3);
            this.updateCalendar();
        } catch (error) {
            console.error('Error navigating to previous days:', error);
        }
    }

    nextDays() {
        try {
            this.currentDate.setDate(this.currentDate.getDate() + 3);
            this.updateCalendar();
        } catch (error) {
            console.error('Error navigating to next days:', error);
        }
    }

    showError(message) {
        OC.Notification.showTemporary(message, { type: 'error' });
    }

    showSuccess(message) {
        OC.Notification.showTemporary(message, { type: 'success' });
    }

    // Predefined colors for desks
    getDeskColor(deskId) {
        const colors = [
            '#3498db', // Blue
            '#e74c3c', // Red
            '#2ecc71', // Green
            '#f39c12', // Orange
            '#9b59b6', // Purple
            '#1abc9c', // Turquoise
            '#34495e', // Dark Gray
            '#e67e22', // Carrot
            '#95a5a6', // Gray
            '#8e44ad', // Violet
            '#16a085', // Dark Turquoise
            '#27ae60'  // Dark Green
        ];
        return colors[deskId % colors.length];
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    try {
        window.deskBookingApp = new DeskBookingApp();
    } catch (error) {
        console.error('Failed to initialize Desk Booking App:', error);
    }
});
