-- Adds 'other' to payments.payment_type so students can submit "Other Fees"
-- via upload_payment.php without the INSERT failing under strict SQL mode.
ALTER TABLE `payments`
    MODIFY `payment_type` enum('full','installment','other') DEFAULT 'full';
