-- Phase 4B: Ziina Payment Intent API support without webhook
-- Run after Phase 4 migration.

ALTER TABLE payment_records
  ADD COLUMN IF NOT EXISTS provider_payment_intent_id VARCHAR(190) NULL AFTER provider_reference,
  ADD COLUMN IF NOT EXISTS provider_status VARCHAR(80) NULL AFTER provider_payment_intent_id,
  ADD COLUMN IF NOT EXISTS provider_payload JSON NULL AFTER provider_status;

CREATE INDEX IF NOT EXISTS idx_payment_records_intent_id ON payment_records (provider_payment_intent_id);
