
# 🌐 Council ERP - Namecheap Hosting Deployment

## Prerequisites

- Namecheap hosting account with PHP 8.2+ support
- MySQL database access
- SSH/cPanel access
- Composer available (or ability to upload vendor folder)

## Step 1: Prepare Your Files

1. **Update Environment Configuration**
   - Edit `.env` file with your Namecheap database credentials
   - Set `APP_ENV=production` and `APP_DEBUG=false`
   - Update `APP_URL` to your domain

2. **Install Dependencies Locally**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

## Step 2: Database Setup

1. **Create MySQL Database** in cPanel
   - Note down: database name, username, password

2. **Update `.env` with Database Credentials**
   ```
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

## Step 3: File Upload

1. **Upload all files** to your hosting account
   - Upload to public_html or your domain folder
   - Ensure `vendor/` folder is included
   - Set file permissions: 644 for files, 755 for folders

2. **Set Storage Permissions**
   ```bash
   chmod -R 775 storage/
   chmod -R 775 bootstrap/cache/
   ```

## Step 4: Domain Configuration

1. **Point Domain Root** to the `public/` folder
   - In cPanel, set Document Root to `/public_html/public/`
   - Or create a symbolic link if needed

2. **URL Rewrite** should work with included `.htaccess`

## Step 5: Installation

1. **Access Installation Page**
   - Visit: `https://yourdomain.com/install`
   - Follow the installation wizard

2. **Generate Application Key**
   - The installer will generate this automatically
   - Or manually run: `php artisan key:generate`

## Step 6: Security

1. **Remove Installation Routes** (after installation)
2. **Set Proper File Permissions**
3. **Enable HTTPS** in Namecheap SSL settings

## Common Issues

- **500 Error**: Check storage permissions and .env file
- **Database Connection**: Verify database credentials
- **Missing Dependencies**: Ensure vendor folder is uploaded
- **URL Issues**: Check Document Root points to public folder

## Support

Contact your Namecheap support team for hosting-specific issues.
