<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

# Desk Booking Nextcloud App - Development Guidelines

## Project Overview
This is a Nextcloud plugin for booking office desks with custom timeslots. The app provides:
- Configurable number of desks
- Global visibility of all bookings
- Real-time updates
- Admin management interface
- RESTful API

## Architecture
- **Backend**: PHP using Nextcloud App Framework
- **Frontend**: Vanilla JavaScript with Nextcloud's UI framework
- **Database**: Uses Nextcloud's database abstraction layer
- **Routing**: Nextcloud's routing system

## Code Style Guidelines

### PHP
- Use strict typing (`declare(strict_types=1);`)
- Follow PSR-12 coding standards
- Use dependency injection through constructors
- Implement proper error handling with try-catch blocks
- Use type hints for all parameters and return types

### JavaScript
- Use modern ES6+ features
- Implement proper error handling
- Use async/await for API calls
- Follow consistent naming conventions
- Add proper JSDoc comments for functions

### CSS
- Use CSS custom properties (variables) when possible
- Follow BEM methodology for class naming
- Ensure responsive design
- Use Nextcloud's CSS variables for theming

## Database Guidelines
- Use entities that extend `OCP\AppFramework\Db\Entity`
- Implement mappers that extend `OCP\AppFramework\Db\QBMapper`
- Use proper indexing for performance
- Include created_at and updated_at timestamps

## Security Considerations
- Validate all user inputs
- Use CSRF tokens for forms
- Implement proper authorization checks
- Sanitize output data
- Use parameterized queries

## API Design
- Follow RESTful conventions
- Use appropriate HTTP status codes
- Return consistent JSON responses
- Include proper error messages
- Implement pagination for large datasets

## Testing
- Write unit tests for services and mappers
- Test API endpoints thoroughly
- Include edge cases and error scenarios
- Test across different browsers

## File Organization
- Controllers in `lib/Controller/`
- Services in `lib/Service/`
- Database entities in `lib/Db/`
- Frontend assets in `js/`, `css/`, `img/`
- Templates in `templates/`

## Best Practices
- Keep controllers thin, move business logic to services
- Use dependency injection
- Implement proper logging
- Follow Nextcloud app development guidelines
- Ensure accessibility compliance
