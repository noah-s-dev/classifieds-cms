# 🏪 Classifieds CMS

A modern, responsive classified advertisements content management system built with PHP and MySQL. This project provides a complete solution for creating local marketplace websites where users can buy, sell, and trade items.

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.1.3
- **Icons**: Bootstrap Icons 1.7.2
- **Fonts**: Inter (Google Fonts)
- **Server**: Apache/Nginx
- **Security**: PDO, Prepared Statements, Input Validation
- **File Upload**: Image processing and validation

## 📋 Project Overview

Classifieds CMS is a full-featured marketplace platform that enables users to:
- Browse and search classified advertisements
- Post new ads with images and detailed descriptions
- Manage personal advertisements
- Contact sellers through various channels
- Filter ads by category, location, and price range

The system is designed with security, performance, and user experience in mind, featuring a modern responsive design that works seamlessly across all devices.

## ✨ Key Features

### 🔍 Search & Discovery
- Advanced search with multiple filters (keyword, category, location, price range)
- Sort options (newest, oldest, price low/high, popularity)
- Category-based browsing
- Related ads suggestions

### 📱 User Management
- User registration and authentication
- Profile management
- Password security with bcrypt hashing
- Session management

### 📝 Ad Management
- Create, edit, and delete advertisements
- Image upload with validation
- Rich text descriptions
- Price and location information
- Ad status management (active, inactive, sold, expired)

### 🎨 Modern UI/UX
- Responsive design for all devices
- Modern gradient-based color scheme
- Smooth animations and transitions
- Intuitive navigation
- Accessibility compliant

### 🔒 Security Features
- SQL injection protection
- XSS prevention
- CSRF protection
- Input validation and sanitization
- File upload security
- Rate limiting

### 📊 Admin Features
- Default admin account
- Category management
- User management capabilities
- System monitoring

## 👥 User Roles

### **Guest Users**
- Browse all advertisements
- Search and filter ads
- View ad details
- Contact information display

### **Registered Users**
- All guest user features
- Post new advertisements
- Manage personal ads (edit, delete)
- Upload images for ads
- Contact sellers directly

### **Administrators**
- All registered user features
- Manage categories
- Monitor system activity
- User management
- Content moderation

## 📁 Project Structure

```
classifieds-cms/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── main.js            # JavaScript functionality
│   ├── images/                # Static images
│   └── uploads/               # User uploaded images
├── config/
│   ├── database.php           # Database configuration
│   └── security.php           # Security settings
├── includes/
│   ├── auth.php               # Authentication functions
│   ├── ads.php                # Ad management functions
│   ├── search.php             # Search functionality
│   └── security.php           # Security utilities
├── sql/
│   └── database_setup.sql     # Database schema
├── index.php                  # Homepage
├── login.php                  # User login
├── register.php               # User registration
├── post_ad.php                # Create new ad
├── view_ad.php                # View ad details
├── edit_ad.php                # Edit existing ad
├── my_ads.php                 # User's ads management
├── logout.php                 # User logout
├── error.php                  # Error handling
├── .htaccess                  # Server configuration
└── README.md                  # Project documentation
```

## 🚀 Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for dependency management)

### Installation Steps

1. **Clone or Download the Project**
   ```bash
   git clone [https://github.com/noah-s-dev/classifieds-cms]
   cd classifieds-cms
   ```

2. **Configure Web Server**
   - Place the project in your web server's document root
   - Ensure the `assets/uploads/` directory is writable
   ```bash
   chmod 755 assets/uploads/
   ```

3. **Database Setup**
   - Create a new MySQL database
   - Import the database schema:
   ```bash
   mysql -u root -p your_database_name < sql/database_setup.sql
   ```

4. **Configuration**
   - Edit `config/database.php` with your database credentials
   - Update database connection settings:
     - DB_HOST (usually 'localhost')
     - DB_NAME (your database name)
     - DB_USER (database username)
     - DB_PASS (database password)

5. **Security Configuration**
   - Review `config/security.php` for security settings
   - Adjust file upload limits if needed
   - Configure rate limiting parameters

6. **Access the Application**
   - Open your browser and navigate to the project URL
   - Default admin credentials:
     - Username: `admin`
     - Password: `admin123`
   - **Important**: Change the default admin password immediately

### Development Setup
- Enable error reporting in PHP for development
- Set up a local development environment (XAMPP, WAMP, or similar)
- Configure your IDE for PHP development

## 📖 Usage

### For End Users

1. **Browsing Ads**
   - Visit the homepage to see recent ads
   - Use the search bar to find specific items
   - Filter by category, location, or price range
   - Click on ads to view detailed information

2. **Posting Ads**
   - Register for an account or log in
   - Click "Post New Ad" button
   - Fill in ad details (title, description, price, location)
   - Upload images (optional but recommended)
   - Submit and wait for approval

3. **Managing Ads**
   - Access "My Ads" from the navigation menu
   - Edit or delete your existing ads
   - Monitor ad performance and views

### For Administrators

1. **System Management**
   - Log in with admin credentials
   - Monitor user activity and ad submissions
   - Manage categories and system settings
   - Handle user support requests

2. **Content Moderation**
   - Review and approve new ads
   - Remove inappropriate content
   - Manage user accounts

## 🎯 Intended Use

### **Personal Use**
- Create a local marketplace for your community
- Sell personal items online
- Connect buyers and sellers in your area

### **Small Business**
- Establish an online presence for local businesses
- Create a community marketplace
- Generate leads and sales opportunities

### **Educational Purposes**
- Learn PHP and MySQL development
- Study web application security
- Understand content management systems

### **Development & Customization**
- Use as a foundation for custom marketplace applications
- Extend functionality for specific business needs
- Integrate with existing systems

## 📄 License

**License for RiverTheme**

RiverTheme makes this project available for demo, instructional, and personal use. You can ask for or buy a license from [RiverTheme.com](https://RiverTheme.com) if you want a pro website, sophisticated features, or expert setup and assistance. A Pro license is needed for production deployments, customizations, and commercial use.

**Disclaimer**

The free version is offered "as is" with no warranty and might not function on all devices or browsers. It might also have some coding or security flaws. For additional information or to get a Pro license, please get in touch with [RiverTheme.com](https://RiverTheme.com).