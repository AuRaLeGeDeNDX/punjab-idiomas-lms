# Redis Configuration for Institute LMS

## Overview
This document provides instructions for setting up Redis for the Institute LMS system to enable high-performance caching, session management, and queue processing.

## Redis Installation

### Windows (using Chocolatey)
```bash
choco install redis-64
```

### Windows (using WSL2)
```bash
sudo apt update
sudo apt install redis-server
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

### macOS (using Homebrew)
```bash
brew install redis
brew services start redis
```

### Linux (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install redis-server
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

## Configuration

### Environment Variables
Update your `.env` file with the following Redis configuration:

```env
# Redis Configuration
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Use Redis for sessions and caching
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Redis database assignments
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_DB=3
```

### Testing Redis Connection
Run the following command to test Redis connectivity:

```bash
php artisan tinker
>>> Redis::ping()
```

Should return: `"+PONG"`

## Performance Benefits

### Session Management
- **Database sessions**: Each request requires database queries
- **Redis sessions**: In-memory storage with sub-millisecond access times
- **Scalability**: Supports multiple application servers sharing sessions

### Caching
- **Database cache**: Limited by disk I/O and database connections
- **Redis cache**: Memory-based with advanced data structures
- **Persistence**: Optional data persistence with configurable strategies

### Queue Processing
- **Database queues**: Polling overhead and potential deadlocks
- **Redis queues**: Push/pop operations with blocking capabilities
- **Reliability**: Built-in job retry and failure handling

## Security Considerations

### Production Configuration
```env
# Use password authentication
REDIS_PASSWORD=your-secure-password

# Bind to specific interface
REDIS_HOST=127.0.0.1

# Use SSL/TLS for remote connections
REDIS_SCHEME=tls
```

### Redis Configuration File (redis.conf)
```conf
# Bind to localhost only
bind 127.0.0.1

# Require password
requirepass your-secure-password

# Disable dangerous commands
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command DEBUG ""
```

## Monitoring and Maintenance

### Redis CLI Commands
```bash
# Connect to Redis
redis-cli

# Monitor real-time commands
redis-cli monitor

# Check memory usage
redis-cli info memory

# List all keys (development only)
redis-cli keys "*"
```

### Laravel Commands
```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# View queue status
php artisan queue:work --once
```

## Fallback Strategy

The system is designed to gracefully fallback to database-based storage if Redis is unavailable:

1. **Automatic Detection**: Laravel will detect Redis connectivity issues
2. **Graceful Degradation**: Falls back to database sessions/cache
3. **Error Logging**: Connection issues are logged for monitoring
4. **Health Checks**: Built-in Redis health monitoring

## Implementation Notes

- Redis is configured but not required for basic functionality
- The system will work with database-based sessions/cache as fallback
- For production deployments, Redis is highly recommended for performance
- All authentication and authorization features work regardless of Redis availability