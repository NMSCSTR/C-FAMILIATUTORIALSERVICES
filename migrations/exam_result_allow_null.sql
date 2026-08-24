-- Allows exam scores to be cleared (NULL) from admin_enrollments.php.
-- The UI treats an empty field as "not taken yet"; NOT NULL columns made
-- clearing a grade fail or silently coerce to 0.
ALTER TABLE `exam_result`
    MODIFY `diagnostic_exam` int(11) DEFAULT NULL,
    MODIFY `preboard_exam` int(11) DEFAULT NULL,
    MODIFY `compre_exam` int(11) DEFAULT NULL;
