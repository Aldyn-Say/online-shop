-- PostgreSQL: таблица для LoggerService::errorToDb()
CREATE TABLE IF NOT EXISTS application_logs (
    id SERIAL PRIMARY KEY,
    level VARCHAR(32) NOT NULL DEFAULT 'error',
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_application_logs_created_at ON application_logs (created_at DESC);
