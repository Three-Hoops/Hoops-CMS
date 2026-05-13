# Security Policy

## Supported Versions

Hoops-CMS is currently in active development. Security fixes are applied to the `main` branch only.

| Branch | Supported |
|--------|-----------|
| `main` | ✅ Yes    |
| Older branches | ❌ No |

## Reporting a Vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**

Use [GitHub's private vulnerability reporting](https://github.com/Three-Hoops/Hoops-CMS/security/advisories/new) to submit a report. This keeps the details confidential while we work on a fix.

### What to include

- A clear description of the vulnerability and its potential impact
- Steps to reproduce (proof-of-concept code or a minimal reproduction is very helpful)
- The version or commit SHA where you found it
- Any suggested mitigations, if you have them

### What to expect

- **Acknowledgement** within 3 business days
- **Status update** (confirmed, in progress, or won't fix with reasoning) within 10 business days
- A fix and disclosure timeline agreed with you before anything is made public

We follow **coordinated disclosure**: we ask that you give us reasonable time to patch before publishing details publicly.

## Scope

This is a CMS admin panel. Areas of particular concern include:

- Authentication and session handling
- Role-based access control (editor vs. viewer vs. super-admin)
- HTML content sanitisation (TipTap/rich text output)
- SQL injection via Eloquent or raw queries
- Cross-site scripting (XSS) in rendered content
- Cross-site request forgery (CSRF)
- Insecure file upload or path traversal

## Out of Scope

- Vulnerabilities in third-party dependencies (report those upstream)
- Issues that require physical access to the server
- Social engineering attacks
- Rate limiting on non-sensitive endpoints

## Security Measures in Place

- CSRF protection on all state-changing routes (Laravel default)
- Security headers middleware (CSP, X-Frame-Options, HSTS, etc.)
- Session regeneration on login and absolute session timeout
- Rate limiting on the login endpoint
- HTML output sanitised with HTMLPurifier (server-side) and DOMPurify (client-side)
- Dependency vulnerability scanning in CI (Composer and npm audits)
- Password complexity enforcement
