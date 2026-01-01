# AstroFlux Admin Panel - Installation Guide

## 📋 Prerequisites

- WAMP/XAMPP/LAMP installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled

---

## 🚀 Installation Steps

### Step 1: Database Setup

1. **Start WAMP/XAMPP**
   - Make sure MySQL is running

2. **Import Database**
   ```bash
   # Option 1: Using phpMyAdmin
   - Go to http://localhost/phpmyadmin
   - Click "Import"
   - Select: c:\wamp64\www\astroflux\database.sql
   - Click "Go"

   # Option 2: Using MySQL command line
   mysql -u root -p < c:\wamp64\www\astroflux\database.sql
   ```

### Step 2: Configuration

1. **Update Database Credentials** (if needed)
   - Edit: `config/database.php`
   - Change DB_USER and DB_PASS if not using defaults

2. **Update Security Keys** (IMPORTANT!)
   - Edit: `config/security.php`
   - Change JWT_SECRET (line 9)
   - Change ENCRYPTION_KEY (line 27)

### Step 3: File Permissions

```bash
# Make logs directory writable
chmod 755 logs/

# Make uploads directory writable (if using file uploads)
chmod 755 uploads/
```

### Step 4: Test Installation

1. **Access Admin Panel**
   ```
   URL: http://localhost/astroflux/admin/login.php
   ```

2. **Default Login Credentials**
   ```
   Username: admin
   Password: Admin@123456
   ```

3. **IMPORTANT: Change Password Immediately**
   - Go to Settings → Change Password

---

## 📊 Testing API Endpoints

### Test Horoscope API
```
http://localhost/astroflux/api/v1/horoscope?sign=aries&period=daily
```

### Test Tarot API
```
http://localhost/astroflux/api/v1/tarot?action=random&count=1
```

### Test Insights API
```
http://localhost/astroflux/api/v1/insights?period=daily
```

---

## 🔧 Troubleshooting

### Database Connection Error
- Check MySQL is running
- Verify credentials in `config/database.php`
- Check database name is `astroflux_admin`

### 404 Not Found
- Enable mod_rewrite in Apache
- Check `.htaccess` file exists
- Verify AllowOverride is set in Apache config

### Permission Denied
- Check logs/ directory is writable
- Run: `chmod -R 755 astroflux/`

### CORS Errors (when testing from app)
- Update `ALLOWED_ORIGINS` in `config/security.php`
- Add your development URLs

---

## ✅ Post-Installation Checklist

- [ ] Database imported successfully
- [ ] Can login to admin panel
- [ ] Changed default admin password
- [ ] Updated JWT_SECRET
- [ ] Updated ENCRYPTION_KEY
- [ ] Tested all API endpoints
- [ ] Checked logs directory is writable
- [ ] Reviewed security settings
- [ ] Added first horoscope entry
- [ ] Added first tarot card

---

## 🎯 Next Steps

1. **Add Content**
   - Create horoscopes for all zodiac signs
   - Add tarot cards database
   - Create daily/weekly/monthly insights

2. **Connect to Android App**
   - Update API URLs in Android app
   - Test API integration
   - Verify data flow

3. **Production Deployment**
   - Enable HTTPS (uncomment in .htaccess)
   - Update allowed origins for CORS
   - Set up automated backups
   - Monitor logs regularly

---

## 📞 Need Help?

Check the logs:
- `logs/error.log` - PHP errors
- `logs/security.log` - Security events

---

**Installation Complete!** 🎉
