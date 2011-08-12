<?php
/**
 * This file is should contain your local, environment specific configuration.
 * Put all all of those here here (like MySQL configuration)
 * and make sure to ignore this file in your versioning system if you are
 * using one.
 * 
 * Any configuration directives here override those in config.php
 * because it is loaded before it.
 */

namespace melt\core\config {
    const MAINTENANCE_MODE = true;
    const DEVELOPER_KEY = 'c4ceb96eff94';
    
}

namespace melt\db\config {
    const USE_TRIGGER_SEQUENCING = true;
    const STORAGE_ENGINE = 'innodb';
    const PORT = 3306;
    const NAME = 'melt';
    const USER = 'root';
    const PREFIX = '';
    const PASSWORD = '';
    const HOST = '127.0.0.1';
}