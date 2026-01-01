# AstroFlux Admin Panel & API

Play Console Compliant - NO user tracking, content management only

## 🚀 Quick Start

### 1. Database Setup
```bash
# Import database
mysql -u root -p < database.sql
```

### 2. Access Admin Panel
```
URL: http://localhost/astroflux/admin/login.php
Default Login:
- Username: admin
- Password: Admin@123456
```

### 3. Change Default Password
Go to Settings after first login and update your password.

---

## 📁 File Structure

```
astroflux/
├── config/              # Database & security configuration
├── includes/            # Authentication & security functions
├── admin/               # Admin panel (login required)
├── api/v1/              # REST API endpoints
├── assets/css/          # Stylesheets
└── logs/                # Security & admin logs
```

---

## 🔐 Security Features

✅ **Authentication:**
- Argon2ID password hashing
- Session management (1-hour timeout)
- CSRF protection
- Rate limiting (5 login attempts)

✅ **API Security:**
- CORS protection
- Rate limiting (100 requests/minute)
- Input validation & sanitization
- No user tracking

✅ **Play Console Compliance:**
- NO personal user data collection
- NO device tracking
- NO analytics
- Admin actions only logging
- Hashed IP addresses

---

## 📊 API Endpoints

### Horoscope API
```
GET /api/v1/horoscope
Parameters:
- sign: aries|taurus|gemini|cancer|leo|virgo|libra|scorpio|sagittarius|capricorn|aquarius|pisces
- period: daily|weekly|monthly
- date: YYYY-MM-DD (optional, defaults to today)

Example:
/api/v1/horoscope?sign=aries&period=daily
```

### Tarot API
```
GET /api/v1/tarot
Parameters:
- action: random|all
- count: 1-10 (for random, default 1)

Examples:
/api/v1/tarot?action=random&count=1
/api/v1/tarot?action=all
```

### Insights API
```
GET /api/v1/insights
Parameters:
- period: daily|weekly|monthly
- date: YYYY-MM-DD (optional)
- category: cosmic_energy|love|career|health|personal_growth|key_dates (optional)

Example:
/api/v1/insights?period=daily&date=2024-01-01
```

---

## 🛡️ HTTPS Only (Production)

In production, uncomment these lines in `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 📝 Admin Features

1. **Dashboard** - Overview & quick actions
2. **Horoscopes** - Create/manage daily/weekly/monthly horoscopes
3. **Tarot Cards** - Manage tarot card database
4. **Insights** - Create daily/weekly/monthly insights
5. **Settings** - Change password, system settings

---

## ⚠️ Important Notes

1. **Change default credentials** immediately
2. **Update JWT_SECRET** in `config/security.php`
3. **Update ENCRYPTION_KEY** in `config/security.php`
4. **Enable HTTPS** in production
5. **Regular backups** of database
6. **Monitor logs** in `logs/` directory

---

## 🔒 Play Console Compliance Checklist

- ✅ No personal user data collection
- ✅ No device identifiers stored
- ✅ No location tracking
- ✅ No analytics/tracking
- ✅ HTTPS encryption
- ✅ Secure password storage
- ✅ Rate limiting
- ✅ Admin-only logging
- ✅ Hashed IP addresses

---

## 📞 Support

For issues or questions, check the logs:
- `logs/error.log` - PHP errors
- `logs/security.log` - Security events

---

**Built with Play Console compliance in mind** ✅
