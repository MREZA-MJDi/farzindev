# VPS Mail Provider API Contract

This Laravel application is a domain-scoped client of the shared VPS mail service. The provider is installed once on the VPS; each project receives a different API token.

## Create a mailbox

`POST {MAIL_PROVIDER_API_URL}/api/v1/mailboxes`

Headers:

- `Authorization: Bearer <project-scoped-token>`
- `Accept: application/json`
- `Idempotency-Key: <sha256(domain|local-part)>`

JSON body:

```json
{
  "email": "info@farajihome.com",
  "password": "<generated-by-this-project>",
  "quota_mb": 512
}
```

Successful response (HTTP 201):

```json
{
  "success": true,
  "data": {
    "id": "provider-mailbox-id",
    "email": "info@farajihome.com"
  }
}
```

The provider must enforce the token's allowed domain on the server side, even when the request body contains an email address. The provider must reject other domains, never return mailbox passwords, never log request passwords or bearer tokens, and treat a repeated idempotency key as the same provisioning request.

Expected errors:

- HTTP 401/403: missing, invalid, or out-of-scope project token.
- HTTP 409: mailbox already exists and is not being recreated.
- HTTP 422: invalid mailbox address, password, or quota.
- HTTP 5xx: temporary provider failure; the Laravel app must not save credentials locally.

## Project configuration

Set these values in the project's server-side `.env` (never commit real tokens):

- `MAIL_PROVIDER_API_URL`: provider origin, without `/api/v1`.
- `MAIL_PROVIDER_API_TOKEN`: unique token scoped to this project's domain.
- `MAIL_PROVIDER_DOMAIN`: the exact project domain.
- `MAIL_PROVIDER_SMTP_HOST`, `MAIL_PROVIDER_SMTP_PORT`, `MAIL_PROVIDER_SMTP_ENCRYPTION`.
- `MAIL_PROVIDER_IMAP_HOST`, `MAIL_PROVIDER_IMAP_PORT`, `MAIL_PROVIDER_IMAP_ENCRYPTION`.

When the provider and Laravel application share this VPS, bind the API to loopback only (for example, `http://127.0.0.1:8091`) and do not expose that port publicly. For a remote provider, use HTTPS in production. The generated mailbox password is stored only in the existing encrypted SMTP/IMAP password columns.

## IMAP runtime

Inbox reading uses the pure-PHP `webklex/php-imap` 6.2 client. The server does not need the removed native PHP `ext-imap` extension; PHP OpenSSL, mbstring, iconv, fileinfo, libxml, and zip support are required.
