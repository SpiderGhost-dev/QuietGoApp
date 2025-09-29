-- QuietGo Database Schema
-- Professional-grade data architecture for health tracking and AI analysis

-- ============================================================================
-- TABLE: users
-- Stores user accounts and subscription information
-- ============================================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    journey VARCHAR(50) DEFAULT 'best_life',
    subscription_plan VARCHAR(50) DEFAULT 'free',
    subscription_status VARCHAR(50) DEFAULT 'active',
    subscription_start_date DATETIME,
    subscription_end_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_subscription (subscription_plan, subscription_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: photos
-- Metadata for all uploaded photos (files stored on disk)
-- ============================================================================
CREATE TABLE IF NOT EXISTS photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    photo_type ENUM('meal', 'stool', 'symptom') NOT NULL,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    thumbnail_path VARCHAR(500),
    file_size INT,
    mime_type VARCHAR(50),
    upload_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    location_latitude DECIMAL(10, 8),
    location_longitude DECIMAL(11, 8),
    location_accuracy FLOAT,
    context_time VARCHAR(50),
    context_symptoms TEXT,
    context_notes TEXT,
    original_filename VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, photo_type),
    INDEX idx_upload_date (upload_timestamp),
    INDEX idx_photo_type (photo_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: stool_analyses
-- Bristol Stool Scale AI analysis results
-- ============================================================================
CREATE TABLE IF NOT EXISTS stool_analyses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    photo_id INT NOT NULL,
    user_id INT NOT NULL,
    bristol_scale TINYINT,
    bristol_description VARCHAR(255),
    color_assessment VARCHAR(255),
    consistency VARCHAR(50),
    volume_estimate VARCHAR(50),
    confidence_score TINYINT,
    health_insights JSON,
    recommendations JSON,
    reported_symptoms TEXT,
    correlation_note TEXT,
    ai_model VARCHAR(100),
    processing_time FLOAT,
    analysis_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, analysis_timestamp),
    INDEX idx_bristol (bristol_scale),
    INDEX idx_confidence (confidence_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: meal_analyses
-- CalcuPlate AI meal analysis results (Pro+ users)
-- ============================================================================
CREATE TABLE IF NOT EXISTS meal_analyses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    photo_id INT NOT NULL,
    user_id INT NOT NULL,
    foods_detected JSON,
    total_calories INT,
    protein_grams FLOAT,
    carbs_grams FLOAT,
    fat_grams FLOAT,
    fiber_grams FLOAT,
    meal_quality_score VARCHAR(10),
    portion_sizes TEXT,
    nutritional_completeness VARCHAR(10),
    confidence_score TINYINT,
    nutrition_insights JSON,
    recommendations JSON,
    journey_specific_note TEXT,
    ai_model VARCHAR(100),
    model_tier VARCHAR(20),
    cost_tier VARCHAR(20),
    processing_time FLOAT,
    analysis_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, analysis_timestamp),
    INDEX idx_calories (total_calories),
    INDEX idx_confidence (confidence_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: manual_meal_logs
-- Manual meal logging from Pro users
-- ============================================================================
CREATE TABLE IF NOT EXISTS manual_meal_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    photo_id INT,
    meal_type VARCHAR(50),
    meal_time TIME,
    meal_date DATE,
    portion_size VARCHAR(50),
    main_foods TEXT,
    estimated_calories INT,
    protein_grams FLOAT,
    carb_grams FLOAT,
    fat_grams FLOAT,
    hunger_before VARCHAR(50),
    fullness_after VARCHAR(50),
    energy_level TINYINT,
    meal_notes TEXT,
    log_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE SET NULL,
    INDEX idx_user_date (user_id, meal_date),
    INDEX idx_meal_type (meal_type),
    INDEX idx_timestamp (log_timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: symptom_analyses
-- AI analysis of symptom photos
-- ============================================================================
CREATE TABLE IF NOT EXISTS symptom_analyses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    photo_id INT NOT NULL,
    user_id INT NOT NULL,
    symptom_category VARCHAR(255),
    severity_estimate VARCHAR(50),
    visual_characteristics JSON,
    confidence_score TINYINT,
    tracking_recommendations JSON,
    correlation_potential TEXT,
    reported_symptoms TEXT,
    ai_model VARCHAR(100),
    processing_time FLOAT,
    analysis_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, analysis_timestamp),
    INDEX idx_severity (severity_estimate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: ai_cost_tracking
-- Track API usage and costs for analytics
-- ============================================================================
CREATE TABLE IF NOT EXISTS ai_cost_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    photo_type VARCHAR(50),
    ai_model VARCHAR(100),
    model_tier VARCHAR(20),
    cost_estimate DECIMAL(10, 4),
    tokens_used INT,
    processing_time FLOAT,
    request_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, request_timestamp),
    INDEX idx_model_tier (model_tier),
    INDEX idx_cost (cost_estimate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: pattern_correlations
-- Store discovered patterns and correlations for reports
-- ============================================================================
CREATE TABLE IF NOT EXISTS pattern_correlations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    correlation_type VARCHAR(100),
    trigger_data JSON,
    outcome_data JSON,
    confidence_level FLOAT,
    occurrence_count INT DEFAULT 1,
    first_detected TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_detected TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, correlation_type),
    INDEX idx_confidence (confidence_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Insert default admin user for testing
-- ============================================================================
INSERT INTO users (email, name, journey, subscription_plan, subscription_status)
VALUES ('admin@quietgo.app', 'Admin User', 'best_life', 'pro_plus', 'active')
ON DUPLICATE KEY UPDATE email = email;
