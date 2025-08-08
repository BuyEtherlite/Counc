# Council ERP System

A comprehensive Enterprise Resource Planning (ERP) system designed specifically for City Councils and Municipal organizations, built with Laravel.

## 🚀 Features

### Core Modules
- **Housing Management**: Waiting lists, allocations, and property management
- **Administrative CRM**: Customer relationship management and service delivery
- **Facility Bookings**: Swimming pools, halls, and recreational facilities
- **Gate Takings**: Revenue management for public facilities
- **Cemeteries & Grave Register**: Cemetery management and record keeping
- **Property Valuations**: Asset valuation and management
- **Lease Management**: Property lease administration
- **Land Management**: Land records and development tracking
- **Town Planning**: Development applications and approvals
- **Architectural Services**: Building plans and approvals
- **Water Connections**: Utility connections and metering
- **Quality Assurance**: Process and service quality management
- **Access Control & Security**: System security and user access
- **Audit Trail**: Complete activity tracking and compliance
- **Customer Services CRM**: Public service management
- **Health Management**: Public health services and records
- **Emergency Services**: Emergency response coordination
- **Finance Modules**: General ledger with QuickBooks compatibility
- **Municipal Billing**: Rates, fees, and payment processing
- **Stores/Inventory**: Supply chain and asset management
- **Committee Administration**: Meeting and governance management

### System Features
- **Easy Installation**: Step-by-step setup wizard
- **Multi-Office Support**: Multiple site offices and departments
- **Role-Based Access Control**: Granular permissions management
- **Module-Based Architecture**: Enable/disable modules per department
- **Data Import/Export**: Integration with existing accounting systems
- **Responsive Design**: Mobile-friendly interface
- **Audit Logging**: Complete activity tracking
- **Multi-tenant Ready**: Support for multiple councils

## 📋 Installation

### Requirements
- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Web server (Apache/Nginx)

### Quick Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/BuyEtherlite/Counc.git
   cd Counc
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run the installation wizard**
   - Navigate to your domain in a web browser
   - Click "Start Installation"
   - Follow the step-by-step setup process:
     - Configure site settings
     - Set up database connection
     - Create admin user account
     - Enter council details
   - Complete installation

### Manual Setup (Alternative)

1. **Configure database**
   Edit `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=council_erp
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

2. **Run migrations**
   ```bash
   php artisan migrate
   ```

3. **Create admin user**
   ```bash
   php artisan tinker
   >>> User::create([
   ...   'name' => 'Admin User',
   ...   'email' => 'admin@council.gov',
   ...   'password' => Hash::make('secure_password'),
   ...   'role' => 'super_admin',
   ...   'is_active' => true
   ... ]);
   ```

## 🔧 Configuration

### Department Setup
1. Login as super admin
2. Navigate to Administration > Departments
3. Create departments (e.g., Housing, Finance, Planning)
4. Assign module access permissions to each department

### Office Management
1. Go to Administration > Offices
2. Create office locations
3. Link offices to departments
4. Assign users to specific offices

### User Management
1. Access Administration > Users
2. Create user accounts
3. Assign roles and departments
4. Set module permissions

### Module Configuration
- Each module can be enabled/disabled per department
- Granular permissions control access to specific features
- Admin can toggle sidebar items for different user roles

## 🏗️ Architecture

### Database Schema
- **Users**: User accounts and authentication
- **Councils**: Municipal organization details
- **Departments**: Organizational units
- **Offices**: Physical locations/branches
- **Module-specific tables**: Each ERP module has dedicated tables

### Security
- Role-based access control (RBAC)
- Department-level permissions
- Module-level access control
- Audit trail for all activities
- Secure password policies

### Integration
- **Accounting Software**: Import data from QuickBooks, Sage, etc.
- **API Endpoints**: RESTful API for external integrations
- **Export Capabilities**: Data export in various formats

## 📖 Usage

### For Super Administrators
- Configure system settings
- Manage departments and offices
- Create and manage user accounts
- Control module access permissions
- Monitor system activity

### For Department Managers
- Manage department users
- Access assigned modules
- Generate department reports
- Monitor departmental activities

### For End Users
- Access modules based on assigned permissions
- Perform daily operational tasks
- Generate reports and documents
- Update records and data

## 🔒 Security Features

- **Authentication**: Secure login system
- **Authorization**: Role and permission-based access
- **Audit Trail**: Complete activity logging
- **Data Protection**: Encrypted sensitive data
- **Session Management**: Secure session handling
- **Access Control**: IP and time-based restrictions

## 🚀 Getting Started

After installation:

1. **Initial Setup**
   - Login with admin credentials created during installation
   - Configure council information
   - Set up departments and offices

2. **User Management**
   - Create department heads and managers
   - Add operational users
   - Assign appropriate roles and permissions

3. **Module Configuration**
   - Enable required modules for each department
   - Configure module-specific settings
   - Import existing data if applicable

4. **Training and Deployment**
   - Train users on their assigned modules
   - Implement gradual rollout by department
   - Monitor usage and adjust permissions as needed

## 📞 Support

For installation support, configuration assistance, or module customization, please refer to the documentation or contact the development team.

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

**Built for Municipal Excellence** 🏛️