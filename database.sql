-- AstroFlux Admin Panel Database
-- Play Console Compliant: NO personal user data collection
-- Only stores admin users and content (horoscopes, tarot, insights)

CREATE DATABASE IF NOT EXISTS astroflux_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE astroflux_admin;

-- =============================================
-- ADMIN USERS TABLE
-- Only for admin panel users, NOT app users
-- =============================================
CREATE TABLE admin_users (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'editor') DEFAULT 'editor',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME NULL,
    login_attempts INT DEFAULT 0,
    lockout_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default super admin (password: Admin@123456)
INSERT INTO admin_users (username, email, password_hash, role) VALUES
('admin', 'admin@astroflux.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- =============================================
-- ADMIN AUDIT LOG
-- Track admin actions only, NOT app user actions
-- =============================================
CREATE TABLE admin_audit_log (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    admin_id INT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT UNSIGNED,
    changes_json JSON NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- HOROSCOPE CONTENT
-- Content to serve to app, no user data
-- =============================================
CREATE TABLE horoscopes (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    zodiac_sign ENUM('aries','taurus','gemini','cancer','leo','virgo',
                     'libra','scorpio','sagittarius','capricorn','aquarius','pisces') NOT NULL,
    period ENUM('daily','weekly','monthly') NOT NULL,
    target_date DATE NOT NULL COMMENT 'Date this horoscope is for',
    content TEXT NOT NULL,
    love_score TINYINT UNSIGNED DEFAULT 0,
    career_score TINYINT UNSIGNED DEFAULT 0,
    health_score TINYINT UNSIGNED DEFAULT 0,
    lucky_number VARCHAR(20),
    lucky_color VARCHAR(30),
    lucky_time VARCHAR(50),
    mood VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_horoscope (zodiac_sign, period, target_date),
    INDEX idx_zodiac_period (zodiac_sign, period),
    INDEX idx_target_date (target_date),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TAROT CARDS
-- Static tarot card data
-- =============================================
CREATE TABLE tarot_cards (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    card_type ENUM('major_arcana','minor_arcana') NOT NULL,
    suit ENUM('cups','wands','swords','pentacles','none') DEFAULT 'none',
    number TINYINT,
    emoji VARCHAR(10),  
    image_url VARCHAR(255),
    meaning_upright TEXT NOT NULL,
    meaning_reversed TEXT NOT NULL,
    description TEXT,
    keywords VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (card_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSIGHTS (Daily/Weekly/Monthly)
-- Content for insights screen
-- =============================================
CREATE TABLE insights (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    period ENUM('daily','weekly','monthly') NOT NULL,
    target_date DATE NOT NULL,
    category ENUM('cosmic_energy','love','career','health','personal_growth','key_dates') NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    icon VARCHAR(10),
    color_code VARCHAR(7) COMMENT 'Hex color code',
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_period_date (period, target_date),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- API RATE LIMITING
-- Prevent API abuse (security, not user tracking)
-- =============================================
CREATE TABLE api_rate_limits (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL COMMENT 'Hashed IP for privacy',
    endpoint VARCHAR(100) NOT NULL,
    request_count INT UNSIGNED DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ip_endpoint (ip_address, endpoint),
    INDEX idx_window_start (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Auto cleanup old rate limit data (older than 1 hour)
CREATE EVENT IF NOT EXISTS cleanup_rate_limits
ON SCHEDULE EVERY 1 HOUR
DO DELETE FROM api_rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 HOUR);

-- =============================================
-- SYSTEM SETTINGS
-- App configuration (no user data)
-- =============================================
CREATE TABLE system_settings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string','integer','boolean','json') DEFAULT 'string',
    description VARCHAR(255),
    updated_by INT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('app_version', '1.0.0', 'string', 'Current app version'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode'),
('api_rate_limit', '100', 'integer', 'API requests per minute'),
('enable_logging', '1', 'boolean', 'Enable audit logging');

-- =============================================
-- NOTES
-- =============================================
-- COMPLIANCE NOTES:
-- 1. NO user personal data is stored (names, emails, phone numbers from app users)
-- 2. NO device identifiers are stored
-- 3. NO location data is stored
-- 4. NO tracking or analytics data
-- 5. IP addresses in rate limiting are for security only, should be hashed
-- 6. Only admin users and content are stored
-- 7. This complies with Google Play Console Data Safety requirements
-- =============================================
