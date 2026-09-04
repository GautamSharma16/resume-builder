# Server and CDN Configuration

The application includes Laravel middleware for canonical host redirects, HSTS on secure production requests, and cache headers for static assets. The web server/CDN should also be configured as follows.

## Preferred Domain

Use `https://cvbliss.in` as the preferred origin and set:

```env
APP_URL=https://cvbliss.in
APP_CANONICAL_HOST=cvbliss.in
```

Redirect `https://www.cvbliss.in/*` to `https://cvbliss.in/*` with a permanent `301`. The Apache `.htaccess` file includes this rule for Apache deployments.

## HSTS

Enable HSTS only after HTTPS is valid for the production site:

```http
Strict-Transport-Security: max-age=31536000
```

Do not add `includeSubDomains` or `preload` until every subdomain is confirmed to support HTTPS.

## HTTP/2

HTTP/2 is controlled by the web server/CDN rather than Laravel. Enable HTTP/2 on the TLS virtual host or CDN zone. For Apache this normally requires `mod_http2` and `Protocols h2 http/1.1`; for Nginx use `listen 443 ssl http2`.

## Compression

Enable Brotli when available and Gzip/Deflate as a fallback for text assets:

```text
text/html
text/css
application/javascript
application/json
application/xml
image/svg+xml
```

The Apache `.htaccess` file includes common Deflate/Brotli directives, but some hosts require enabling these modules at the server level.

## CAPTCHA

Cloudflare Turnstile protects user login and registration when these env vars are present:

```env
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
TURNSTILE_TIMEOUT=5
```
