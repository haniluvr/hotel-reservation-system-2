# API Endpoints Quick Reference

## Public Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/search-suggestions` | Get search suggestions | No |
| GET | `/api/promo-codes/validate` | Validate promo code | No |
| GET | `/api/reservations/{id}` | Get reservation (limited info) | No |
| GET | `/hotels/{hotelId}/reviews` | Get hotel reviews | No |
| POST | `/api/webhooks/xendit` | Xendit payment webhook | No (signature) |

## Authenticated Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/reservations` | List user reservations | Yes |
| POST | `/api/reservations` | Create reservation | Yes |
| GET | `/api/reservations/{id}` | Get reservation details | Yes |
| PUT | `/api/reservations/{id}` | Update reservation | Yes |
| DELETE | `/api/reservations/{id}` | Cancel reservation | Yes |
| GET | `/reviews/create/{reservationId}` | Show review form | Yes |
| POST | `/reviews` | Create review | Yes |
| GET | `/reviews/{id}/edit` | Edit review | Yes |
| PUT | `/reviews/{id}` | Update review | Yes |
| DELETE | `/reviews/{id}` | Delete review | Yes |

## Web Routes

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/` | Homepage | No |
| GET | `/hotels/search` | Search hotels | No |
| GET | `/hotels/{id}` | Hotel details | No |
| GET | `/hotels/{hotelId}/rooms/{roomId}` | Room details | No |
| GET | `/accommodations` | List accommodations | No |
| GET | `/booking/create` | Booking form | No |
| POST | `/booking` | Create booking | Yes |
| GET | `/bookings` | User bookings | Yes |
| GET | `/bookings/{id}` | Booking details | Yes |
| GET | `/bookings/{id}/cancel` | Cancellation form | Yes |
| POST | `/bookings/{id}/cancel` | Process cancellation | Yes |
| GET | `/payments/checkout/{reservationId}` | Payment checkout | Yes |
| POST | `/payments/process/{reservationId}` | Process payment | Yes |
| GET | `/payments/success/{reservationId}` | Payment success | Yes |
| GET | `/payments/failure/{reservationId}` | Payment failure | Yes |

