-- Phase 28: Media Buyer Agreement, Terms Acceptance, and Commission Agreement
-- This is not legal advice. Owner should have the agreement reviewed by a lawyer before real use.

CREATE TABLE IF NOT EXISTS media_buyer_agreement_templates (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  version VARCHAR(40) NOT NULL,
  content LONGTEXT NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  requires_reacceptance TINYINT(1) NOT NULL DEFAULT 0,
  created_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_media_agreement_template_active (active, requires_reacceptance),
  CONSTRAINT fk_media_agreement_template_owner FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_buyer_agreement_acceptances (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  media_buyer_id BIGINT UNSIGNED NOT NULL,
  template_id BIGINT UNSIGNED NOT NULL,
  template_version VARCHAR(40) NOT NULL,
  accepted_content_snapshot LONGTEXT NOT NULL,
  typed_name VARCHAR(190) NOT NULL,
  signature_data TEXT NULL,
  ip_hash CHAR(64) NULL,
  user_agent_hash CHAR(64) NULL,
  accepted_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_media_acceptance_buyer (media_buyer_id, accepted_at),
  INDEX idx_media_acceptance_template (template_id, template_version),
  CONSTRAINT fk_media_acceptance_buyer FOREIGN KEY (media_buyer_id) REFERENCES media_buyer_profiles(id) ON DELETE CASCADE,
  CONSTRAINT fk_media_acceptance_template FOREIGN KEY (template_id) REFERENCES media_buyer_agreement_templates(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO media_buyer_agreement_templates (title, version, content, active, requires_reacceptance)
SELECT
  'Media Buyer / Marketing Partner Agreement',
  '1.0',
  'MEDIA BUYER / MARKETING PARTNER AGREEMENT\n\nImportant: This template is not legal advice. It must be reviewed by a qualified lawyer before real use.\n\n1. Scope of Marketing Services\nThe Media Buyer / Marketing Partner may promote Habiba Nabil Arabic Academy using approved marketing channels, campaigns, content, and tracking links.\n\n2. Commission Rules\nCommission is calculated according to the rate or fixed amount configured by the Owner in the platform. The dashboard and backend records are the source of truth for clicks, attributed orders, paid orders, revenue, and commissions.\n\n3. Paid Orders Only\nCommission is earned only on orders marked as paid and verified by the Owner or payment workflow. Pending, pending verification, failed, cancelled, and refunded orders do not qualify for payable commission.\n\n4. Refunds, Chargebacks, and Reversals\nIf an order is refunded, charged back, cancelled, or reversed after commission is created, the Owner may reverse, deduct, or reject the related commission.\n\n5. Payout Schedule\nPayouts follow the payout cycle configured by the Owner. Commission may require Owner approval before payout.\n\n6. Ad Spend Responsibility\nUnless otherwise agreed in writing, the Media Buyer is responsible for their own advertising spend, campaign setup, and optimization.\n\n7. Tracking Links and Attribution\nThe Media Buyer must use the approved tracking links, partner codes, and UTM parameters. Attribution depends on platform tracking and may use first-touch, last-touch, or manual attribution as configured by the Owner.\n\n8. Brand Usage Rules\nThe Media Buyer must not use misleading claims, unauthorized offers, false discounts, or unapproved brand assets. The Owner may request removal or correction of any campaign.\n\n9. Confidentiality\nThe Media Buyer must keep private business, student, parent, pricing, campaign, and platform information confidential.\n\n10. Data Privacy\nThe Media Buyer must not request, store, expose, or misuse private student or parent data. The Media Buyer dashboard does not grant access to private student/parent learning data.\n\n11. No Access to Private Learning Data\nThe Media Buyer will not access homework, scenarios, reviews, level test answers, recordings, parent notes, student notes, or private learning records.\n\n12. Commission Approval Rights\nThe Owner has the right to approve, reject, reverse, or mark commissions as paid according to platform records, payment status, refund status, and internal review.\n\n13. Termination\nEither party may end the marketing relationship with notice. The Owner may disable tracking links or account access for policy violations, misuse, fraud, or brand risk.\n\n14. Governing Law / Jurisdiction Placeholder\nThis section must be reviewed and completed by a lawyer before real use.\n\n15. Acceptance\nBy typing their legal name and accepting this agreement, the Media Buyer confirms that they understand and agree to the terms above.',
  1,
  0
WHERE NOT EXISTS (SELECT 1 FROM media_buyer_agreement_templates WHERE active = 1);

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public) VALUES
('media_buyer_agreement_required','1','media_buyers','boolean',0),
('media_buyer_agreement_pdf_enabled','0','media_buyers','boolean',0)
ON DUPLICATE KEY UPDATE setting_group=VALUES(setting_group), value_type=VALUES(value_type), is_public=VALUES(is_public);
