# 🚀 Council ERP Deployment Guide

This guide will help you properly deploy Council ERP to your hosting provider and access the installation page.

## 📋 Prerequisites

Before uploading to your hosting provider, ensure you have:

- A web hosting account with PHP 8.2+ support
- MySQL/MariaDB database
- SSH/Terminal access (recommended) or file manager access
- Composer access on your hosting (most hosting providers support this)

## 🔧 Step-by-Step Deployment

### Option 1: Full Upload with Composer (Recommended)

1. **Upload all project files** to your hosting's web directory (usually `public_html`, `www`, or `htdocs`)
   ```
   ✅ Upload everything including:
   - app/
   - config/
   - database/
   - resources/
   - routes/
   - storage/
   - public/
   - composer.json
   - .env.example
   - artisan
   ```

2. **Access your hosting terminal/SSH** and navigate to your website directory

3. **Install dependencies** using Composer:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Set correct permissions**:
   ```bash
   chmod -R 775 storage/
   chmod -R 775 bootstrap/cache/
   ```

5. **Point your domain** to the `public/` folder (very important!)

6. **Access your website** - you should now see the installation page

### Option 2: Upload with Pre-built Dependencies

If you don't have Composer access on your hosting:

1. **On your local machine**, run:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. **Upload all files** including the generated `vendor/` directory

3. **Set permissions** (if you have SSH access):
   ```bash
   chmod -R 775 storage/
   chmod -R 775 bootstrap/cache/
   ```

4. **Point your domain** to the `public/` folder

5. **Access your website**

## 🌐 Web Server Configuration

### Apache (.htaccess)
The `public/.htaccess` file should handle URL rewriting automatically.

### Nginx
Add this to your Nginx configuration:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Document Root
**IMPORTANT**: Your domain should point to the `public/` folder, not the root project folder.

**Correct setup:**
```
domain.com → /path/to/council-erp/public/
```

**Incorrect setup:**
```
domain.com → /path/to/council-erp/
```

## 🚨 Common Issues and Solutions

### Issue: "White screen" or "500 Error"
**Cause**: Missing dependencies or permissions
**Solution**: 
1. Ensure `vendor/` directory exists and contains files
2. Run `composer install --no-dev` on the hosting
3. Check storage permissions: `chmod -R 775 storage/`

### Issue: "Application key not set"
**Cause**: Missing or empty APP_KEY in .env
**Solution**: The install system will auto-generate this, but you can manually create it:
```bash
php artisan key:generate
```

### Issue: Still not seeing install page
**Cause**: Domain not pointing to public folder
**Solution**: Configure your hosting to point to the `public/` directory

### Issue: "Class not found" errors
**Cause**: Composer autoloader not properly installed
**Solution**: 
1. Delete `vendor/` folder
2. Run `composer install --no-dev --optimize-autoloader`
3. Upload the complete `vendor/` folder

## 📂 Required File Structure

After upload, your hosting should have this structure:
```
your-domain.com/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/           ← Domain points here
│   ├── index.php
│   └── .htaccess
├── resources/
├── routes/
├── storage/          ← Must be writable
├── vendor/           ← Must contain dependencies
├── .env              ← Will be created during install
├── composer.json
└── artisan
```

## 🔧 Hosting-Specific Notes

### Shared Hosting
- Most shared hosting providers support Composer
- Some require you to use their terminal or file manager
- Contact support if you need help running Composer commands

### VPS/Dedicated Servers
- You have full control - follow Option 1 above
- Make sure PHP 8.2+ is installed
- Ensure MySQL/MariaDB is available

### Popular Hosting Providers

**cPanel Hosting:**
1. Upload files via File Manager or FTP
2. Use Terminal in cPanel to run `composer install`
3. Set domain document root to `public/` folder

**DigitalOcean/Vultr/AWS:**
1. SSH into your server
2. Upload files and run composer commands
3. Configure Nginx/Apache properly

## ✅ Verification

After deployment, you should see:

1. **Installation page** at `https://yourdomain.com/install`
2. **System requirements check** showing green checkmarks
3. **Database connection test** working
4. **Successful installation** leading to the login page

## 🆘 Need Help?

If you're still having issues:

1. Check your hosting provider's documentation for PHP/Laravel support
2. Verify PHP version is 8.2 or higher: `php --version`
3. Ensure all required PHP extensions are installed
4. Contact your hosting provider's support team

## 🔐 Security Notes

- Never upload your local `.env` file with database credentials
- The system will create a fresh `.env` during installation
- Always set proper file permissions
- Keep your Laravel application updated