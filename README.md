# Desk Booking - Nextcloud App

A simple desk booking system for office spaces that allows users to book desks for custom time slots with global visibility across all users.

## Installation

1. **Download**: Place the `deskbooking` folder in your Nextcloud `apps/` directory
2. **Enable**: Go to Nextcloud Apps and enable "Desk Booking"
3. **Configure**: Visit Admin Settings > Desk Booking to set up your desks

## Configuration

1. Navigate to **Admin Settings > Desk Booking**
2. Click "Add New Desk"
3. Fill in the desk details:
   - **Name**: Unique identifier for the desk
   - **Description**: Optional description
   - **Location**: Physical location in the office
   - **Status**: Active/Inactive

## Technical Details

### Database Schema

**Desks Table** (`deskbooking_desks`):
- `id` - Primary key
- `name` - Desk name (unique)
- `description` - Optional description
- `location` - Physical location
- `is_active` - Active status
- `created_at` / `updated_at` - Timestamps

**Bookings Table** (`deskbooking_bookings`):
- `id` - Primary key
- `desk_id` - Foreign key to desks
- `user_id` - Nextcloud user ID
- `start_time` / `end_time` - Booking time range
- `notes` - Optional notes
- `created_at` / `updated_at` - Timestamps

### API Endpoints

**Desk Management**:
- `GET /api/desks` - List all active desks
- `POST /api/desks` - Create new desk
- `PUT /api/desks/{id}` - Update desk
- `DELETE /api/desks/{id}` - Delete desk

**Booking Management**:
- `GET /api/bookings` - List all bookings
- `GET /api/bookings/my` - User's bookings
- `GET /api/bookings/desk/{deskId}` - Bookings for specific desk
- `POST /api/bookings` - Create booking
- `PUT /api/bookings/{id}` - Update booking
- `DELETE /api/bookings/{id}` - Delete booking
- `PATCH /api/bookings/{id}/cancel` - Cancel booking

### Requirements

- **Nextcloud**: Version 30 or higher, other versions untested
- **PHP**: Version 8.0 or higher, other versions untested
- **Database**: MySQL or MariaDB, other engines untested
