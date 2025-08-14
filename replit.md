# Overview

This is a complete WordPress plugin called "Dosmax Activity Log" that integrates with existing WP Activity Log database tables to provide role-based activity filtering. The plugin displays activity logs only for users with specific roles (like site-admin) while completely hiding activities from other roles (like administrator) at the database query level. It features a comprehensive admin interface with sortable columns, pagination, and detailed AJAX-powered event information viewing.

## Recent Changes (August 13, 2025)

✓ Built complete plugin architecture with proper WordPress hooks and structure
✓ Implemented role-based database filtering using WP Activity Log tables (*_wsal_occurrences and *_wsal_metadata)
✓ Created admin interface with sortable columns for Date, User, IP, Severity, Object, Event Type, and Message
✓ Added AJAX functionality for "More details..." expandable views showing comprehensive event metadata
✓ Implemented pagination and performance optimizations with database indexing
✓ Added CSS styling for WordPress admin integration and responsive design
✓ Created JavaScript interactions for dynamic content loading and user experience
✓ Added external database configuration support with custom host, credentials, and table prefixes
✓ Built comprehensive settings page at Settings → Dosmax Activity Log for database and role configuration
✓ Implemented database connection testing and validation with real-time status feedback
✓ Enhanced "More details" display to match WP Activity Log format with proper metadata formatting
✓ Added automatic fallback from external to WordPress database if connection fails
✓ Set up demo environment with comprehensive plugin documentation
✓ Added comprehensive filtering system with user, object, IP address, and date filters
✓ Implemented before/after/on date filtering with date picker interface
✓ Created filter summary display showing active filters with tag-based UI
✓ Built responsive filter interface with proper WordPress admin styling
✓ Reorganized admin menu structure with standalone "Activity Log" menu containing both logs and settings

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## WordPress Plugin Architecture
The project follows standard WordPress plugin architecture patterns with organized asset separation for CSS and JavaScript files. The plugin integrates with WordPress's admin interface and leverages the platform's built-in hooks, actions, and AJAX handling mechanisms.

## Frontend Components
- **Admin Interface**: Custom admin panel for displaying activity logs in a tabular format with sortable columns
- **Interactive Elements**: AJAX-powered expandable detail views for log entries using jQuery
- **Responsive Design**: Clean, WordPress-compatible styling with hover effects and proper spacing

## Data Management
- **Activity Logging**: Captures user activities, security events, and system occurrences with metadata including severity levels, timestamps, user information, and IP addresses
- **Structured Data Storage**: Stores log entries with unique occurrence IDs for detailed tracking and retrieval
- **Expandable Details**: Supports detailed information storage and on-demand loading for each log entry

## AJAX Architecture
- **Dynamic Content Loading**: Uses WordPress AJAX endpoints for loading detailed log information without page refreshes
- **Security Implementation**: Includes nonce verification for secure AJAX requests
- **Error Handling**: Implements proper error handling and user feedback for failed requests

## User Interface Design
- **Table-Based Layout**: Uses sortable columns for date, user, IP address, and severity information
- **Progressive Disclosure**: Implements "More details" functionality to reduce cognitive load while providing access to comprehensive information
- **WordPress Integration**: Follows WordPress admin design patterns and conventions for seamless integration

# External Dependencies

## WordPress Core
- **WordPress Framework**: Built as a WordPress plugin leveraging core functionality, admin interfaces, and security features
- **WordPress AJAX**: Uses WordPress's built-in AJAX handling system for dynamic content loading
- **WordPress Admin**: Integrates with WordPress admin dashboard and follows admin UI conventions

## JavaScript Libraries
- **jQuery**: Uses jQuery for DOM manipulation, event handling, and AJAX requests (included with WordPress core)

## WordPress APIs
- **Admin Menu API**: For creating admin interface pages
- **AJAX API**: For handling asynchronous requests and responses
- **Security API**: For nonce verification and secure request handling