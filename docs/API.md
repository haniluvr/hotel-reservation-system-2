# Hotel Reservation System API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication

Most API endpoints require authentication. Include the authentication token in the request header:

```
Authorization: Bearer {token}
```

For web-based authentication, use Laravel's session-based authentication.

---

## Endpoints

### 1. Search Suggestions

Get search suggestions for hotels and locations.

**Endpoint:** `GET /api/search-suggestions`

**Query Parameters:**
- `q` (string, required): Search query

**Response:**
```json
{
  "hotels": [
    {
      "id": 1,
      "name": "Belmont Hotel",
      "city": "Manila",
      "country": "Philippines"
    }
  ],
  "cities": ["Manila", "Cebu"]
}
```

---

### 2. Promo Code Validation

Validate a promo code.

**Endpoint:** `GET /api/promo-codes/validate`

**Query Parameters:**
- `code` (string, required): Promo code to validate
- `amount` (float, required): Total amount to apply discount to

**Response (Valid):**
```json
{
  "valid": true,
  "code": "SUMMER2024",
  "discount_type": "percentage",
  "discount_value": 10,
  "discount_amount": 100.00,
  "final_amount": 900.00
}
```

**Response (Invalid):**
```json
{
  "valid": false,
  "message": "Promo code not found or expired"
}
```

**Error Codes:**
- `400`: Invalid request parameters
- `404`: Promo code not found

---

### 3. Reservations

#### 3.1 List Reservations

Get a list of user's reservations.

**Endpoint:** `GET /api/reservations`

**Authentication:** Required

**Query Parameters:**
- `status` (string, optional): Filter by status (pending, confirmed, cancelled, completed)
- `check_in_from` (date, optional): Filter from check-in date
- `check_in_to` (date, optional): Filter to check-in date
- `per_page` (integer, optional): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "reservation_number": "BEL202411090001",
        "check_in_date": "2024-11-15",
        "check_out_date": "2024-11-17",
        "status": "confirmed",
        "total_amount": "5000.00",
        "room": {
          "id": 1,
          "room_type": "Deluxe Room",
          "hotel": {
            "id": 1,
            "name": "Belmont Hotel"
          }
        },
        "payment": {
          "id": 1,
          "status": "paid",
          "amount": "5000.00"
        }
      }
    ],
    "total": 10,
    "per_page": 15
  }
}
```

**Error Codes:**
- `401`: Unauthorized

---

#### 3.2 Create Reservation

Create a new reservation.

**Endpoint:** `POST /api/reservations`

**Authentication:** Required

**Request Body:**
```json
{
  "room_id": 1,
  "check_in_date": "2024-11-15",
  "check_out_date": "2024-11-17",
  "adults": 2,
  "children": 0,
  "special_requests": "Late check-in requested",
  "promo_code": "SUMMER2024"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Reservation created successfully",
  "data": {
    "id": 1,
    "reservation_number": "BEL202411090001",
    "status": "pending",
    "total_amount": "5000.00",
    "room": {
      "id": 1,
      "room_type": "Deluxe Room",
      "hotel": {
        "id": 1,
        "name": "Belmont Hotel"
      }
    }
  }
}
```

**Error Codes:**
- `400`: Validation error or room not available
- `401`: Unauthorized
- `422`: Invalid input data

**Validation Errors:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "room_id": ["The room id field is required."],
    "check_in_date": ["The check in date must be a date after or equal to today."]
  }
}
```

---

#### 3.3 Get Reservation

Get details of a specific reservation.

**Endpoint:** `GET /api/reservations/{id}`

**Authentication:** Required (or public with limited info)

**Response (Authenticated):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "reservation_number": "BEL202411090001",
    "check_in_date": "2024-11-15",
    "check_out_date": "2024-11-17",
    "adults": 2,
    "children": 0,
    "status": "confirmed",
    "total_amount": "5000.00",
    "discount_amount": "0.00",
    "special_requests": "Late check-in requested",
    "room": {
      "id": 1,
      "room_type": "Deluxe Room",
      "price_per_night": "2500.00",
      "hotel": {
        "id": 1,
        "name": "Belmont Hotel",
        "address": "123 Main Street",
        "city": "Manila"
      }
    },
    "payment": {
      "id": 1,
      "status": "paid",
      "amount": "5000.00",
      "payment_method": "credit_card"
    },
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Response (Public - Limited Info):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "reservation_number": "BEL202411090001",
    "status": "confirmed",
    "check_in_date": "2024-11-15",
    "check_out_date": "2024-11-17",
    "total_amount": "5000.00",
    "room": {
      "room_type": "Deluxe Room",
      "hotel": {
        "name": "Belmont Hotel"
      }
    }
  }
}
```

**Error Codes:**
- `404`: Reservation not found
- `403`: Unauthorized access

---

#### 3.4 Update Reservation

Update a reservation (limited fields).

**Endpoint:** `PUT /api/reservations/{id}`

**Authentication:** Required

**Request Body:**
```json
{
  "special_requests": "Updated special requests"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Reservation updated successfully",
  "data": {
    "id": 1,
    "special_requests": "Updated special requests"
  }
}
```

**Error Codes:**
- `400`: Invalid update or reservation cannot be modified
- `401`: Unauthorized
- `404`: Reservation not found

---

#### 3.5 Cancel Reservation

Cancel a reservation.

**Endpoint:** `DELETE /api/reservations/{id}`

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "message": "Reservation cancelled successfully"
}
```

**Error Codes:**
- `400`: Reservation cannot be cancelled
- `401`: Unauthorized
- `403`: Not authorized to cancel this reservation
- `404`: Reservation not found

---

### 4. Webhooks

#### 4.1 Xendit Payment Webhook

Receive payment status updates from Xendit.

**Endpoint:** `POST /api/webhooks/xendit`

**Authentication:** None (signature verified)

**Headers:**
- `x-callback-token`: Webhook callback token for verification

**Request Body (Xendit Format):**
```json
{
  "event": "invoice.paid",
  "data": {
    "id": "invoice_id",
    "external_id": "BEL202411090001",
    "status": "PAID",
    "amount": 5000.00,
    "paid_at": "2024-11-09T10:00:00Z"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Webhook processed successfully"
}
```

**Error Codes:**
- `400`: Invalid webhook signature or payload
- `500`: Internal server error

**Supported Events:**
- `invoice.paid`: Payment completed
- `invoice.expired`: Payment expired
- `invoice.voided`: Payment voided

---

## Error Response Format

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error message",
  "error": "Detailed error description (optional)"
}
```

## HTTP Status Codes

- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `500`: Internal Server Error

## Rate Limiting

API endpoints are rate-limited to prevent abuse. Current limits:
- Authenticated users: 60 requests per minute
- Unauthenticated users: 30 requests per minute

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests in current window
- `X-RateLimit-Reset`: Time when rate limit resets

## Pagination

List endpoints support pagination. Response includes:
- `current_page`: Current page number
- `per_page`: Items per page
- `total`: Total number of items
- `last_page`: Last page number
- `data`: Array of items

## Date Format

All dates are in ISO 8601 format: `YYYY-MM-DD` or `YYYY-MM-DDTHH:mm:ssZ`

## Currency

All monetary amounts are in PHP (Philippine Peso) and formatted as decimal numbers with 2 decimal places.

---

## Examples

### Example: Create Reservation with Promo Code

```bash
curl -X POST http://localhost:8000/api/reservations \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "room_id": 1,
    "check_in_date": "2024-11-15",
    "check_out_date": "2024-11-17",
    "adults": 2,
    "children": 0,
    "promo_code": "SUMMER2024"
  }'
```

### Example: Get User Reservations

```bash
curl -X GET "http://localhost:8000/api/reservations?status=confirmed&per_page=10" \
  -H "Authorization: Bearer {token}"
```

### Example: Cancel Reservation

```bash
curl -X DELETE http://localhost:8000/api/reservations/1 \
  -H "Authorization: Bearer {token}"
```

---

## Support

For API support, contact:
- Email: support@belmonthotel.com
- Documentation: https://docs.belmonthotel.com/api

