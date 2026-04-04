-- Issue #82: add JSON positions for constellation map points
ALTER TABLE `constellation`
ADD COLUMN IF NOT EXISTS `positions_const` JSON NULL;
