ALTER TABLE `payments`
  MODIFY `status` enum('paid','pending','failed','refunded','refund_requested','cancelled') DEFAULT 'pending';
