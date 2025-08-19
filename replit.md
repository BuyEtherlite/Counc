# Fuel Management Platform

## Project Overview
A comprehensive fuel management platform with corporate fleet management, admin role controls, transaction limits, and coupon system. Built using modern web technologies with PostgreSQL database integration.

## Project Architecture

### Technology Stack
- **Frontend**: React + Vite, Wouter for routing, TanStack Query for data fetching, Tailwind CSS + shadcn/ui
- **Backend**: Express.js with TypeScript
- **Database**: PostgreSQL with Drizzle ORM
- **Authentication**: Replit Auth integration

### Database Schema
The application uses a comprehensive PostgreSQL database with the following main entities:
- **Users**: Core user management with different user types (individual, corporate, government, merchant, agent, admin)
- **Companies**: Corporate entities for fleet management
- **Vehicles**: Vehicle registration with approval workflow
- **Fuel Balances**: Separate balances per fuel type (petrol, diesel)
- **Transactions**: All fuel-related transactions
- **Coupons**: Fuel coupon system with unique code generation
- **Merchants**: Fuel station management
- **Admin Roles**: Role-based permissions system

### Storage Implementation
- Using `DatabaseStorage` class that implements the `IStorage` interface
- All database operations handled through Drizzle ORM
- Proper TypeScript types generated from schema

## Recent Changes

### August 18, 2025 - Complete Fuel Management Platform Implementation
- ✓ Fixed React rendering errors with fuel balance display
- ✓ Corrected authentication flow and user creation
- ✓ Resolved TypeScript errors in server routes
- ✓ Fixed coupon creation schema validation issues
- ✓ Implemented comprehensive admin dashboard (/admin or /imagine route)
- ✓ Added corporate fleet management interface (/fleet route)
- ✓ Enhanced user management with status controls
- ✓ Vehicle approval queue with document verification
- ✓ Withdrawal request processing system
- ✓ Real-time statistics and system monitoring
- ✓ Multi-role support: admin, corporate, individual, merchant, agent
- ✓ Coupon system with unique code generation and redemption
- ✓ Driver management with fuel limits for corporate accounts
- ✓ Comprehensive API endpoints for all fuel management operations
- ✓ Modern responsive UI with dark mode support
- ✓ Server running on port 5000 with all endpoints functional

## User Preferences
(None documented yet)

## Development Guidelines
- Follow the fullstack_js blueprint architecture
- Use DatabaseStorage for all data operations
- Maintain proper TypeScript typing throughout
- Follow existing patterns for API routes and frontend components

## Environment Variables
The following database environment variables are available:
- DATABASE_URL
- PGHOST, PGPORT, PGUSER, PGPASSWORD, PGDATABASE

## Next Steps
- Start the application workflow to test database connectivity
- Implement frontend components that interact with the database
- Test user registration and authentication flow