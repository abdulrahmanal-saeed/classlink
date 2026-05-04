# Hostinger Final Checklist

## Before Upload

- [ ] Confirm project is PHP + MySQL/MariaDB.
- [ ] Confirm no Node.js build is required.
- [ ] Confirm no secrets are committed.
- [ ] Confirm `.env.example` is ready.
- [ ] Create real `.env` only on the server.
- [ ] Confirm database migrations are ready.
- [ ] Confirm upload folders are documented.
- [ ] Confirm deployment guide is ready.
- [ ] Confirm database guide is ready.
- [ ] Confirm storage guide is ready.
- [ ] Review `docs/SECURITY_AUDIT_REPORT.md`.
- [ ] Review `docs/DEPLOYMENT_CHECKLIST.md`.

## On Hostinger

- [ ] Select the correct domain.
- [ ] Open hPanel → Websites → Dashboard.
- [ ] Create MySQL/MariaDB database.
- [ ] Create database user and password.
- [ ] Import migration files in order.
- [ ] Upload PHP public files to `public_html`.
- [ ] Upload backend/components folders in a protected structure.
- [ ] Add real `.env` outside public access if possible.
- [ ] Create upload folders.
- [ ] Set upload folder permissions.
- [ ] Enable SSL.
- [ ] Confirm PHP version is compatible.
- [ ] Check Hostinger PHP limits for upload sizes.
- [ ] Check error logs.

## After Deployment

- [ ] Homepage opens.
- [ ] Pricing opens.
- [ ] Checkout opens.
- [ ] Thank-you page opens.
- [ ] Login works.
- [ ] Logout works.
- [ ] Owner dashboard works.
- [ ] Student dashboard works.
- [ ] Parent dashboard works.
- [ ] Academy dashboard works.
- [ ] Media buyer dashboard works if enabled.
- [ ] File uploads work.
- [ ] Invalid uploads are rejected.
- [ ] Payment status workflow works.
- [ ] Ziina missing/invalid key fails safely.
- [ ] AI disabled/missing key fails safely.
- [ ] Email logs work if email provider is missing.
- [ ] Notifications work.
- [ ] No browser console errors.
- [ ] Mobile responsive check done.
- [ ] Browser network tab shows no secrets.
- [ ] Public cannot access private uploads.
- [ ] Student cannot access another student data.
- [ ] Parent cannot access unlinked child data.
- [ ] Academy partner cannot access another academy brief.

## Go / No-Go

Launch only when:

- [ ] Security report has no unresolved critical blocker.
- [ ] Upload endpoints are reviewed.
- [ ] Ownership checks are tested.
- [ ] Payment fake-paid scenario is tested.
- [ ] Backups are configured.
- [ ] Owner accepts final QA.
