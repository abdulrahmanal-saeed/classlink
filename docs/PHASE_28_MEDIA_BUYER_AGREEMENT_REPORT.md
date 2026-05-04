# Phase 28 Execution Report

## Phase Name

Media Buyer Contract, Terms Acceptance, and Commission Agreement

## Goal

Add a contract and terms acceptance flow for Media Buyers before they can access their dashboard.

Important: this is not legal advice. The agreement text is editable by the Owner and should be reviewed by a lawyer before real use.

---

## Database Migration

```text
backend/php/database/migrations/028_media_buyer_agreements.sql
```

---

## Files Created

### Backend

```text
backend/php/shared/MediaBuyerAgreement.php
backend/php/database/migrations/028_media_buyer_agreements.sql
```

### Media Buyer pages

```text
web/public/media/agreement/index.php
web/public/media/agreement/accept/index.php
```

### Owner pages

```text
web/public/owner/media-buyers/agreements/index.php
web/public/owner/media-buyers/agreement/index.php
```

---

## Files Updated

```text
backend/php/core/Auth.php
```

---

## New Database Models

### media_buyer_agreement_templates

```text
id
title
version
content
active
requires_reacceptance
created_by_user_id
created_at
updated_at
```

### media_buyer_agreement_acceptances

```text
id
media_buyer_id
template_id
template_version
accepted_content_snapshot
typed_name
signature_data
ip_hash
user_agent_hash
accepted_at
created_at
```

---

## Default Agreement Template

A default editable template was inserted. It covers:

```text
scope of marketing services
commission rules
paid orders only
excluded pending/failed/cancelled/refunded orders
refund and chargeback commission reversal
payout schedule
ad spend responsibility
tracking links and attribution
platform dashboard as source of truth
brand usage rules
confidentiality
data privacy
no access to private student/parent data
termination notice
governing law placeholder
Owner right to approve/reject commissions
```

---

## Media Buyer Flow

Routes:

```text
/media/agreement
/media/agreement/accept
```

Rules:

```text
Media Buyer must accept active agreement before accessing media dashboard.
Accepted agreement snapshot is stored and never changes if template is edited later.
Typed legal name is required.
Optional signature field is supported.
IP and user agent are stored as SHA-256 hashes when available.
Acceptance date/time is stored.
```

---

## Access Control

`require_role('media_buyer')` now checks the agreement status.

If agreement is required and not accepted, the media buyer is redirected to:

```text
/media/agreement
```

The agreement page itself is excluded from the redirect loop.

---

## Owner Flow

Routes:

```text
/owner/media-buyers/agreements
/owner/media-buyers/agreement?id={acceptanceId}
```

Owner can:

```text
View active agreement template
Create a new active agreement template
Mark a new template as requiring re-acceptance
View all accepted agreements
Open accepted agreement snapshot
Print / Save PDF from browser
```

---

## Re-acceptance

If Owner creates a new template version and checks:

```text
Require all media buyers to re-accept this new version
```

then existing media buyers are redirected to `/media/agreement` until they accept the new active version.

---

## PDF Copy

A server-side PDF generator was not added in this phase.

Owner can open the accepted snapshot and use:

```text
Print / Save PDF
```

from the browser.

---

## Audit Logs

Logged actions:

```text
media_buyer_agreement_accepted
media_buyer_agreement_template_saved
```

---

## Security

Implemented:

```text
Media buyer cannot access dashboard until agreement is accepted.
Accepted snapshot is immutable.
Owner can view agreement acceptances.
IP and user agent are hashed, not stored raw.
Media buyer agreement does not grant access to private student/parent data.
```

---

## Manual Test Checklist

```text
Run migration 028
Create or use existing media buyer
Login as media buyer
Confirm redirect to /media/agreement
Try opening /media/dashboard before accepting and confirm redirect
Read agreement
Type legal name
Accept agreement
Confirm redirect to /media/dashboard
Login as Owner
Open /owner/media-buyers/agreements
Confirm acceptance appears
Open accepted agreement snapshot
Print / Save PDF from browser
Create a new template version with re-acceptance enabled
Login as media buyer again
Confirm re-acceptance is required
Check audit log
```

---

## Stop Point

Stop here. Test Media Buyer agreement acceptance before continuing.
