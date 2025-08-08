# Council ERP API Documentation

## Overview
This document describes the REST API endpoints for the Council ERP system.

## Authentication
All API endpoints require authentication using Laravel Sanctum tokens.

### Headers
```
Authorization: Bearer {your-api-token}
Content-Type: application/json
Accept: application/json
```

## Base URL
```
https://your-domain.com/api
```

## Modules

### CRM Module

#### Customers

##### List Customers
```
GET /api/crm/customers
```

Query Parameters:
- `search` (optional): Search by name, email, or customer number
- `status` (optional): Filter by status (active, inactive, suspended)
- `per_page` (optional): Number of items per page (default: 15)

##### Create Customer
```
POST /api/crm/customers
```

Request Body:
```json
{
  "customer_number": "CUST001",
  "first_name": "John",
  "last_name": "Doe", 
  "email": "john.doe@example.com",
  "phone": "1234567890",
  "address": "123 Main St",
  "id_number": "123456789",
  "customer_type": "individual",
  "status": "active"
}
```

##### Get Customer
```
GET /api/crm/customers/{id}
```

##### Update Customer
```
PUT /api/crm/customers/{id}
```

##### Delete Customer
```
DELETE /api/crm/customers/{id}
```

### Facilities Module

#### Facilities

##### List Facilities
```
GET /api/facilities/facilities
```

##### Create Facility
```
POST /api/facilities/facilities
```

Request Body:
```json
{
  "name": "Community Hall",
  "code": "CH001",
  "description": "Main community hall for events",
  "facility_type_id": 1,
  "location": "Downtown",
  "capacity": 200,
  "hourly_rate": 50.00,
  "amenities": ["projector", "sound_system", "kitchen"],
  "status": "active",
  "bookable": true,
  "operating_hours": {
    "monday": "08:00-22:00",
    "tuesday": "08:00-22:00"
  }
}
```

## Error Responses

All endpoints return standardized error responses:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

## Success Responses

All successful responses follow this format:

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data
  }
}
```

## Rate Limiting

API requests are limited to 60 requests per minute per authenticated user.