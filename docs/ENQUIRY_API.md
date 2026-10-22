# Enquiry Intake API

Public endpoint the external website posts enquiries into. Submissions appear in the admin UI under
**Leads → Enquiry**.

## Setup

Generate a key and add it to `.env` on every environment that should accept enquiries:

```bash
php artisan tinker --execute="echo Illuminate\Support\Str::random(64);"
```

```dotenv
ENQUIRY_API_KEY=<the generated 64-character string>
```

Then `php artisan config:clear`. If the key is unset the endpoint returns **503** rather than accepting
unauthenticated writes.

## Endpoint

```
POST https://<host>/api/enquiries
```

| Header | Value |
|---|---|
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |
| `X-API-KEY` | the value of `ENQUIRY_API_KEY` (or send `Authorization: Bearer <key>`) |

Rate limit: **60 requests per minute per IP**. The route is CSRF-exempt.

## Request body

Only `first_name` is required, plus **at least one of** `email` or `phone`. Everything else is optional.

| Field | Type | Notes |
|---|---|---|
| `first_name` | string(100) | **required** |
| `middle_name`, `last_name` | string(100) | |
| `email` | email(150) | required if `phone` absent |
| `phone` | string(20) | required if `email` absent |
| `whatsapp_no` | string(20) | |
| `gender` | string | `M`/`F`/`O` or `Male`/`Female`/`Other` — normalised to one character |
| `birthdate` | date | any format PHP can parse; `YYYY-MM-DD` preferred |
| `standard` | string(100) | free text, e.g. `Standard 9` |
| `board` | string(100) | free text, e.g. `CBSE` |
| `medium` | string(50) | |
| `stream` | string | `Arts`, `Commerce` or `Science` |
| `school_name` | string(255) | |
| `country`, `state`, `district`, `taluka` | string(100) | free text |
| `city_village` | string(100) | |
| `address` | string(500) | |
| `pincode` | 6 digits | |
| `guardian_name` | string(200) | |
| `guardian_contact` | string(20) | |
| `guardian_relation` | string(50) | |
| `language` | string | `en`, `hi` or `gu` (default `en`) |
| `message` | string(2000) | free-text enquiry |
| `source` | string(50) | defaults to `external_api`; use it to distinguish campaigns |

### Master-data resolution

`standard`, `board`, `state`, `district` and `taluka` are sent as **free text** — the caller does not need to
know internal IDs. Each is matched case-insensitively against the corresponding master table:

- an exact match populates the internal ID **and** keeps the original text;
- no match leaves the ID `NULL` and still keeps the text.

Nothing is ever rejected for failing to match. The complete request body is also stored verbatim, so fields
this API does not model are not lost.

## Responses

| Status | Meaning |
|---|---|
| `201` | Created. |
| `200` | Duplicate — an enquiry with the same email arrived within the last 10 minutes. No new row. |
| `401` | Missing or incorrect API key. |
| `422` | Validation failed; see `errors`. |
| `429` | Rate limit exceeded. |
| `503` | `ENQUIRY_API_KEY` is not configured on the server. |
| `500` | Server error. Details are logged, not returned. |

Success:

```json
{
  "success": true,
  "message": "Enquiry received.",
  "data": { "enquiry_id": 123, "status": "New" }
}
```

Duplicate (safe to retry — the response points at the existing record):

```json
{
  "success": true,
  "duplicate": true,
  "message": "Enquiry already received.",
  "data": { "enquiry_id": 123, "status": "New" }
}
```

Validation failure:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": { "email": ["The email field is required when phone is not present."] }
}
```

## Example

```bash
curl -X POST https://<host>/api/enquiries \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -H "X-API-KEY: $ENQUIRY_API_KEY" \
  -d '{
    "first_name": "Riya",
    "last_name": "Patel",
    "email": "riya.patel@example.com",
    "phone": "9876543210",
    "standard": "Standard 9",
    "board": "CBSE",
    "school_name": "Sunrise High School",
    "state": "Gujarat",
    "district": "Surat",
    "city_village": "Adajan",
    "pincode": "395009",
    "guardian_name": "Mahesh Patel",
    "guardian_contact": "9876543211",
    "language": "gu",
    "message": "Please share the programme details and fees."
  }'
```

## Admin workflow

Enquiries land with status `New`. From **Leads → Enquiry** an admin can assign an owner, add remarks and move
the status through `Contacted` → `Converted` / `Closed`. Moving off `New` stamps `contacted_at`. The detail
page shows the raw payload, source IP and user agent for debugging.

Permissions: `11704` (read), `11705` (write), `11706` (manage — update status and delete).
