-- Upgrade from 1.x: add an index on topicid, used by every lookup this
-- plugin does. Safe to run once; if you ever need to run it manually
-- twice, drop the index first (`ALTER TABLE #__kunena_jnlsolved DROP INDEX idx_jnlsolved_topicid;`).
ALTER TABLE `#__kunena_jnlsolved` ADD INDEX `idx_jnlsolved_topicid` (`topicid`);
