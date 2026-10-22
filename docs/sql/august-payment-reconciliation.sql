-- ===========================================================================
-- August 2026 payment reconciliation (1 Aug - 31 Aug)
--
-- Queries 1-3 need nothing but the database.
-- Query 4 compares against a Razorpay payments export and needs the staging
-- table from 4a loaded first.
--
-- All queries exclude leads and soft-deleted students, matching how the
-- application itself scopes Student queries (Student::booted adds a global
-- excludeLeads scope, and the model uses SoftDeletes).
-- ===========================================================================


-- ---------------------------------------------------------------------------
-- 1b. START HERE: how big is the problem, by category?
-- ---------------------------------------------------------------------------
SELECT t.issue, COUNT(*) AS rows_affected FROM (
    SELECT
        CASE
            WHEN p.payment_id IS NULL                                        THEN 'NO_PAYMENT_LOG'
            WHEN p.txnid IS NULL OR p.txnid = ''
              OR p.txnid IN ('N/A','MANUAL_SYNC','MANUAL_SYNC_MIGRATION')    THEN 'PLACEHOLDER_TXNID'
            WHEN p.txnid LIKE 'MIG!_%' ESCAPE '!'                            THEN 'MIGRATION_TXNID'
            WHEN p.txnid NOT LIKE 'pay!_%' ESCAPE '!'                        THEN 'TXNID_NOT_A_RAZORPAY_ID'
            WHEN s.razorpay_payment_id IS NULL OR s.razorpay_payment_id = '' THEN 'STUDENT_MISSING_PAYMENT_ID'
            WHEN s.razorpay_payment_id <> p.txnid                            THEN 'STUDENT_PAYMENT_ID_DIFFERS'
            WHEN s.razorpay_order_id IS NULL OR s.razorpay_order_id = ''     THEN 'STUDENT_MISSING_ORDER_ID'
            WHEN ABS(p.amount - i.total_due) > 0.01                          THEN 'AMOUNT_MISMATCH'
            WHEN i.status <> 'PAID'                                          THEN 'INVOICE_NOT_MARKED_PAID'
            WHEN p.status <> '1'                                             THEN 'PAYMENT_NOT_AUTHORIZED'
            WHEN s.fees_status <> 'Paid'                                     THEN 'STUDENT_NOT_MARKED_PAID'
            ELSE 'OK'
        END AS issue
    FROM invoice i
    JOIN student s ON s.student_id = i.student_id
    LEFT JOIN paymentlog p
      ON p.payment_id = (SELECT MIN(p2.payment_id) FROM paymentlog p2 WHERE p2.invoice_id = i.invoice_id)
    WHERE i.invoice_date >= '2026-08-01'
      AND i.invoice_date <  '2026-09-01'
      AND s.deleted_at IS NULL
      AND s.is_lead = 0
) t
GROUP BY t.issue
ORDER BY rows_affected DESC;


-- ---------------------------------------------------------------------------
-- 1. The detail: every August invoice whose payment data is inconsistent,
--    with the first problem found named in `issue`.
-- ---------------------------------------------------------------------------
SELECT * FROM (
    SELECT
        s.student_id,
        s.fname,
        s.lname,
        s.email,
        s.contact_no,
        s.fees_status,
        s.subscription,
        s.razorpay_order_id   AS student_order_id,
        s.razorpay_payment_id AS student_payment_id,
        i.invoice_id,
        i.invoice_date,
        i.status              AS invoice_status,
        i.total_due,
        p.payment_id,
        p.txnid               AS paymentlog_txnid,
        p.amount              AS paymentlog_amount,
        p.status              AS paymentlog_status,
        p.processdatetime,
        CASE
            WHEN p.payment_id IS NULL                                        THEN 'NO_PAYMENT_LOG'
            WHEN p.txnid IS NULL OR p.txnid = ''
              OR p.txnid IN ('N/A','MANUAL_SYNC','MANUAL_SYNC_MIGRATION')    THEN 'PLACEHOLDER_TXNID'
            WHEN p.txnid LIKE 'MIG!_%' ESCAPE '!'                            THEN 'MIGRATION_TXNID'
            WHEN p.txnid NOT LIKE 'pay!_%' ESCAPE '!'                        THEN 'TXNID_NOT_A_RAZORPAY_ID'
            WHEN s.razorpay_payment_id IS NULL OR s.razorpay_payment_id = '' THEN 'STUDENT_MISSING_PAYMENT_ID'
            WHEN s.razorpay_payment_id <> p.txnid                            THEN 'STUDENT_PAYMENT_ID_DIFFERS'
            WHEN s.razorpay_order_id IS NULL OR s.razorpay_order_id = ''     THEN 'STUDENT_MISSING_ORDER_ID'
            WHEN ABS(p.amount - i.total_due) > 0.01                          THEN 'AMOUNT_MISMATCH'
            WHEN i.status <> 'PAID'                                          THEN 'INVOICE_NOT_MARKED_PAID'
            WHEN p.status <> '1'                                             THEN 'PAYMENT_NOT_AUTHORIZED'
            WHEN s.fees_status <> 'Paid'                                     THEN 'STUDENT_NOT_MARKED_PAID'
            ELSE 'OK'
        END AS issue
    FROM invoice i
    JOIN student s ON s.student_id = i.student_id
    LEFT JOIN paymentlog p
      ON p.payment_id = (SELECT MIN(p2.payment_id) FROM paymentlog p2 WHERE p2.invoice_id = i.invoice_id)
    WHERE i.invoice_date >= '2026-08-01'
      AND i.invoice_date <  '2026-09-01'
      AND s.deleted_at IS NULL
      AND s.is_lead = 0
) t
WHERE t.issue <> 'OK'
ORDER BY t.issue, t.invoice_id;


-- ---------------------------------------------------------------------------
-- 2. Students marked Paid with NO invoice at all.
--    There is no invoice to date-filter on, so this uses the student's own
--    updated_at as the August window.
-- ---------------------------------------------------------------------------
SELECT
    s.student_id, s.fname, s.lname, s.email,
    s.fees_status, s.subscription,
    s.razorpay_order_id, s.razorpay_payment_id,
    s.created_at, s.updated_at
FROM student s
WHERE s.fees_status = 'Paid'
  AND s.deleted_at IS NULL
  AND s.is_lead = 0
  AND s.updated_at >= '2026-08-01'
  AND s.updated_at <  '2026-09-01'
  AND NOT EXISTS (SELECT 1 FROM invoice i WHERE i.student_id = s.student_id)
ORDER BY s.student_id;


-- ---------------------------------------------------------------------------
-- 3. Duplicate August invoices: same student, same plan, more than once.
--    (the snapshot has e.g. HARSH TANK on INV-000192 and INV-000193)
-- ---------------------------------------------------------------------------
SELECT
    s.student_id, s.fname, s.lname, s.email,
    ii.description             AS plan,
    COUNT(*)                   AS invoice_count,
    GROUP_CONCAT(i.invoice_id) AS invoice_ids,
    GROUP_CONCAT(i.total_due)  AS totals
FROM invoice i
JOIN student s       ON s.student_id = i.student_id
JOIN invoice_item ii ON ii.invoice_id = i.invoice_id
WHERE i.invoice_date >= '2026-08-01'
  AND i.invoice_date <  '2026-09-01'
  AND s.deleted_at IS NULL
  AND s.is_lead = 0
GROUP BY s.student_id, s.fname, s.lname, s.email, ii.description
HAVING COUNT(*) > 1
ORDER BY invoice_count DESC, s.student_id;


-- ===========================================================================
-- 4. Compare against the Razorpay payments export  (MySQL)
-- ===========================================================================

-- 4a. Staging table. Create it, then import the Razorpay CSV with phpMyAdmin:
--     Import tab -> CSV -> tick "The first line of the file contains the
--     column names". Column order matches the export exactly.
DROP TABLE IF EXISTS rzp_import;
CREATE TABLE rzp_import (
    id                 VARCHAR(64),
    amount             DECIMAL(14,2),
    currency           VARCHAR(8),
    status             VARCHAR(32),
    order_id           VARCHAR(64),
    invoice_id         VARCHAR(64),   -- Razorpay's inv_..., NOT our invoice_id
    international      VARCHAR(16),
    method             VARCHAR(32),
    amount_refunded    DECIMAL(14,2),
    amount_transferred DECIMAL(14,2),
    refund_status      VARCHAR(32),
    captured           VARCHAR(16),
    description        VARCHAR(255),
    card_id            VARCHAR(64),
    card               VARCHAR(64),
    bank               VARCHAR(64),
    wallet             VARCHAR(64),
    vpa                VARCHAR(128),
    email              VARCHAR(255),
    contact            VARCHAR(32),
    notes              TEXT,
    fee                DECIMAL(14,2),
    tax                DECIMAL(14,2),
    error_code         VARCHAR(64),
    error_description  VARCHAR(255),
    created_at         VARCHAR(64),
    card_type          VARCHAR(32),
    card_network       VARCHAR(32),
    Auth_code          VARCHAR(32),
    Payments_ARN       VARCHAR(64),
    Payments_RRN       VARCHAR(64),
    flow               VARCHAR(32),
    source_channel     VARCHAR(32),
    KEY idx_rzp_id (id),
    KEY idx_rzp_order (order_id),
    KEY idx_rzp_email (email)
);

-- 4b. RUN THIS BEFORE TRUSTING 4c-4e: is `amount` in paise or rupees?
--     If max_amount is ~450000 for a 4500 fee it is PAISE, and you must
--     replace `r.amount` with `r.amount / 100` in the queries below.
SELECT
    COUNT(*)    AS rows_loaded,
    MIN(amount) AS min_amount,
    MAX(amount) AS max_amount,
    AVG(amount) AS avg_amount,
    SUM(CASE WHEN amount = ROUND(amount / 100) * 100 THEN 1 ELSE 0 END) AS whole_hundreds
FROM rzp_import;

-- 4b-ii. CHECK THE DATE FORMAT TOO. Razorpay exports created_at either as a
--     'YYYY-MM-DD HH:MM:SS' string or as a unix timestamp (e.g. 1755859500).
--     Run this: if sample_created_at is all digits, the STR_TO_DATE predicate
--     in 4c will silently match nothing, and you must swap it for the
--     FROM_UNIXTIME variant noted there.
SELECT created_at AS sample_created_at,
       CASE WHEN created_at REGEXP '^[0-9]+$' THEN 'unix timestamp' ELSE 'date string' END AS looks_like
FROM rzp_import
LIMIT 5;

-- 4c. In Razorpay but NOT in our database.
--     Money we collected but never recorded. This is the important one.
SELECT
    r.id AS razorpay_payment_id, r.order_id, r.email, r.contact,
    r.amount, r.status, r.method, r.created_at, r.notes,
    s.student_id AS matched_student_id, s.fname, s.lname, s.fees_status
FROM rzp_import r
LEFT JOIN student s
       ON s.razorpay_payment_id = r.id
       OR s.razorpay_order_id   = r.order_id
       OR LOWER(s.email)        = LOWER(r.email)
WHERE r.status IN ('captured', 'authorized')
  -- Date-string form. If 4b-ii said "unix timestamp", replace these two lines
  -- with:  AND FROM_UNIXTIME(r.created_at) >= '2026-08-01'
  --        AND FROM_UNIXTIME(r.created_at) <  '2026-09-01'
  AND STR_TO_DATE(LEFT(r.created_at, 19), '%Y-%m-%d %H:%i:%s') >= '2026-08-01'
  AND STR_TO_DATE(LEFT(r.created_at, 19), '%Y-%m-%d %H:%i:%s') <  '2026-09-01'
  AND NOT EXISTS (SELECT 1 FROM paymentlog p WHERE p.txnid = r.id)
ORDER BY r.created_at;

-- 4d. In our database but NOT in Razorpay.
SELECT
    s.student_id, s.fname, s.lname, s.email,
    i.invoice_id, i.invoice_date, i.total_due, i.status AS invoice_status,
    p.txnid, p.amount AS paymentlog_amount
FROM invoice i
JOIN student s ON s.student_id = i.student_id
LEFT JOIN paymentlog p
       ON p.payment_id = (SELECT MIN(p2.payment_id) FROM paymentlog p2 WHERE p2.invoice_id = i.invoice_id)
WHERE i.invoice_date >= '2026-08-01'
  AND i.invoice_date <  '2026-09-01'
  AND s.deleted_at IS NULL
  AND s.is_lead = 0
  AND (p.txnid IS NULL OR NOT EXISTS (SELECT 1 FROM rzp_import r WHERE r.id = p.txnid))
ORDER BY i.invoice_id;

-- 4e. Present in both, but the AMOUNT disagrees.
--     Use `r.amount / 100` instead of `r.amount` if 4b said paise.
SELECT
    s.student_id, s.fname, s.lname, s.email,
    i.invoice_id, i.invoice_date,
    i.total_due AS our_invoice_total,
    p.amount    AS our_payment_amount,
    r.amount    AS razorpay_amount,
    r.status    AS razorpay_status,
    r.id        AS razorpay_payment_id
FROM paymentlog p
JOIN invoice i    ON i.invoice_id = p.invoice_id
JOIN student s    ON s.student_id = i.student_id
JOIN rzp_import r ON r.id = p.txnid
WHERE i.invoice_date >= '2026-08-01'
  AND i.invoice_date <  '2026-09-01'
  AND s.deleted_at IS NULL
  AND s.is_lead = 0
  AND ABS(r.amount - i.total_due) > 0.01
ORDER BY ABS(r.amount - i.total_due) DESC;
