/**
 * Desk Booking Admin - JavaScript
 */

class DeskBookingAdmin {
    constructor() {
        this.desks = [];
        this.init();
    }

    async init() {
        await this.loadDesks();
        this.setupEventListeners();
    }

    async loadDesks() {
        try {
            const response = await fetch(OC.generateUrl('/apps/deskbooking/admin/api/desks'), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'requesttoken': OC.requestToken
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to load desks');
            }
            
            this.desks = await response.json();
            this.renderDesksTable();
        } catch (error) {
            console.error('Error loading desks:', error);
            this.showError('Failed to load desks');
        }
    }

    setupEventListeners() {
        // Add desk button
        document.getElementById('add-desk-btn').addEventListener('click', () => {
            this.openDeskModal();
        });

        // Modal close events
        document.querySelectorAll('.modal-close, .modal-cancel').forEach(element => {
            element.addEventListener('click', () => {
                this.closeModals();
            });
        });

        // Modal click outside to close
        document.getElementById('desk-modal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                this.closeModals();
            }
        });

        // Desk form submission
        document.getElementById('desk-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleDeskSubmit();
        });
    }

    renderDesksTable() {
        const tbody = document.querySelector('#desks-table tbody');
        
        tbody.innerHTML = this.desks.map(desk => `
            <tr data-desk-id="${desk.id}">
                <td>${desk.name}</td>
                <td>${desk.description || ''}</td>
                <td>${desk.location || ''}</td>
                <td class="status ${desk.isActive ? 'active' : 'inactive'}">
                    ${desk.isActive ? 'Active' : 'Inactive'}
                </td>
                <td>
                    <button class="edit-desk-btn" data-desk-id="${desk.id}">Edit</button>
                    <button class="delete-desk-btn" data-desk-id="${desk.id}">Delete</button>
                </td>
            </tr>
        `).join('');

        // Add event listeners for desk actions
        tbody.querySelectorAll('.edit-desk-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const deskId = parseInt(e.target.dataset.deskId);
                const desk = this.desks.find(d => d.id === deskId);
                this.openDeskModal(desk);
            });
        });

        tbody.querySelectorAll('.delete-desk-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const deskId = parseInt(e.target.dataset.deskId);
                this.deleteDesk(deskId);
            });
        });
    }

    openDeskModal(desk = null) {
        const modal = document.getElementById('desk-modal');
        const form = document.getElementById('desk-form');
        const title = document.getElementById('desk-modal-title');

        if (desk) {
            title.textContent = 'Edit Desk';
            this.populateDeskForm(desk);
        } else {
            title.textContent = 'Add New Desk';
            form.reset();
            document.getElementById('desk-active').checked = true;
        }

        modal.style.display = 'flex';
    }

    populateDeskForm(desk) {
        document.getElementById('desk-id').value = desk.id;
        document.getElementById('desk-name').value = desk.name;
        document.getElementById('desk-description').value = desk.description || '';
        document.getElementById('desk-location').value = desk.location || '';
        document.getElementById('desk-active').checked = desk.isActive;
    }

    closeModals() {
        document.getElementById('desk-modal').style.display = 'none';
    }

    async handleDeskSubmit() {
        const form = document.getElementById('desk-form');
        const formData = new FormData(form);

        const deskData = {
            name: formData.get('name'),
            description: formData.get('description'),
            location: formData.get('location'),
            isActive: formData.get('isActive') === 'on'
        };

        const deskId = document.getElementById('desk-id').value;
        const isEdit = deskId && deskId !== '';

        try {
            const url = isEdit 
                ? OC.generateUrl(`/apps/deskbooking/api/desks/${deskId}`)
                : OC.generateUrl('/apps/deskbooking/api/desks');
            
            const method = isEdit ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'requesttoken': OC.requestToken
                },
                body: JSON.stringify(deskData)
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || `Failed to ${isEdit ? 'update' : 'create'} desk`);
            }

            this.showSuccess(`Desk ${isEdit ? 'updated' : 'created'} successfully!`);
            this.closeModals();
            await this.loadDesks();

        } catch (error) {
            console.error('Error saving desk:', error);
            this.showError(error.message);
        }
    }

    async deleteDesk(deskId) {
        const desk = this.desks.find(d => d.id === deskId);
        if (!desk) return;

        if (!confirm(`Are you sure you want to delete the desk "${desk.name}"? This action cannot be undone.`)) {
            return;
        }

        try {
            const response = await fetch(OC.generateUrl(`/apps/deskbooking/api/desks/${deskId}`), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'requesttoken': OC.requestToken
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to delete desk');
            }

            this.showSuccess('Desk deleted successfully!');
            await this.loadDesks();

        } catch (error) {
            console.error('Error deleting desk:', error);
            this.showError(error.message);
        }
    }

    showError(message) {
        OC.Notification.showTemporary(message, { type: 'error' });
    }

    showSuccess(message) {
        OC.Notification.showTemporary(message, { type: 'success' });
    }
}

// Initialize the admin when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.adminApp = new DeskBookingAdmin();
});
