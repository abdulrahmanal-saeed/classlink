# Hostinger Deployment Addendum — Phase 28

## Phase 28

Media Buyer Contract, Terms Acceptance, and Commission Agreement.

## Important

This agreement template is not legal advice. It should be reviewed by a lawyer before real use.

## Database Migration

Run after Phase 27:

```text
backend/php/database/migrations/028_media_buyer_agreements.sql
```

Export a database backup first.

## Backend Files

```text
backend/php/shared/MediaBuyerAgreement.php
backend/php/database/migrations/028_media_buyer_agreements.sql
backend/php/core/Auth.php
```

## Media Buyer Pages

```text
web/public/media/agreement/index.php
web/public/media/agreement/accept/index.php
```

## Owner Pages

```text
web/public/owner/media-buyers/agreements/index.php
web/public/owner/media-buyers/agreement/index.php
```

## URLs to Test

```text
/media/agreement
/media/dashboard
/owner/media-buyers/agreements
/owner/media-buyers/agreement?id={acceptanceId}
```

## Manual Test

```text
Run migration 028
Login as media buyer
Confirm dashboard redirects to agreement
Accept with typed legal name
Confirm dashboard opens after acceptance
Login as Owner
Open agreements page
View accepted snapshot
Use browser print to save PDF
Create new template version with re-acceptance
Confirm media buyer must accept again
Check audit log
```

## Current Limitations

```text
Server-side PDF generation is not implemented.
Use browser Print / Save PDF for accepted agreement copy.
Owner media buyer detail page can later show agreement status directly if needed.
```

Stop here. Test Phase 28 before continuing.
