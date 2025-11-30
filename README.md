# WorkNPay - Service Marketplace Platform

A mobile-first e-commerce platform connecting customers with verified skilled and semi-skilled workers in Ghana's informal sector, featuring integrated Paystack payment processing and escrow functionality.

## 🚀 Features

### For Customers
- Browse and search for workers by category
- View worker profiles with ratings and reviews
- Book specific services with transparent pricing
- Secure payment processing via Paystack
- Track booking status in real-time
- Rate and review workers
- Open disputes if needed (48-hour window)

### For Workers
- Create professional profile
- List multiple services with individual pricing
- Accept/decline booking requests
- Upload job completion photos
- Request payouts (instant or 24-hour)
- Respond to customer disputes
- Track earnings and statistics

### For Administrators
- Comprehensive dashboard with statistics
- User management (customers, workers, admins)
- Booking management and monitoring
- Dispute resolution (view both sides)
- Payout processing
- Platform reports and analytics

## 💻 Tech Stack

- **Backend**: PHP 8.x
- **Database**: MySQL 8.0
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Payment Gateway**: Paystack API
- **Architecture**: MVC Pattern

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Paystack account (for payments)

## 🛠️ Installation

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/worknpay.git
   cd worknpay
   ```

2. **Create database**
   ```bash
   mysql -u root -p
   CREATE DATABASE worknpay;
   exit;
   ```

3. **Import database**
   ```bash
   mysql -u root -p worknpay < db/dbforlab.sql
   ```

4. **Configure database credentials**
   
   Copy `settings/db_cred.example.php` to `settings/db_cred.php` and update:
   ```php
   define("SERVER", "localhost");
   define("USERNAME", "your_username");
   define("PASSWD", "your_password");
   define("DATABASE", "worknpay");
   ```

5. **Configure Paystack**
   
   Copy `settings/paystack_config.example.php` to `settings/paystack_config.php` and add your keys:
   ```php
   define('PAYSTACK_SECRET_KEY', 'your_secret_key');
   define('PAYSTACK_PUBLIC_KEY', 'your_public_key');
   ```

6. **Set file permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/completion_photos/
   chmod 755 uploads/profile_photos/
   ```

7. **Start development server**
   ```bash
   php -S localhost:8000
   ```

8. **Access the application**
   
   Open http://localhost:8000 in your browser

## 🌐 Deployment

### Free Hosting Options

See [FREE_HOSTING_GUIDE.md](FREE_HOSTING_GUIDE.md) for detailed instructions on deploying to:
- InfinityFree (Recommended)
- 000webhost
- Awardspace
- FreeHosting.com

### Quick Deployment Steps

1. Sign up for free hosting
2. Create MySQL database
3. Upload files via FTP/File Manager
4. Import database via phpMyAdmin
5. Update configuration files
6. Enable SSL certificate
7. Test and go live!

## 📱 Service Categories

- Gadget Repair (phones, laptops, tablets)
- Electrical Services (wiring, appliances, installations)
- Plumbing (repairs, installations)
- Tutoring (academic subjects)

## 💳 Payment Features

- **Escrow System**: Payments held until service completion
- **Multi-Channel**: Mobile money, cards, bank transfers
- **Commission**: 12% total (7% customer + 5% worker)
- **Instant Payout**: 2% fee for immediate withdrawal
- **24-Hour Payout**: Free next-day withdrawal
- **Refund Management**: Automated dispute-based refunds

## 🔒 Security

- Password hashing with bcrypt
- Session-based authentication
- SQL injection prevention
- XSS protection
- CSRF protection
- Payment verification
- Secure escrow system

## 📖 Documentation

- [Deployment Guide](DEPLOYMENT_READY.md) - Complete deployment instructions
- [Free Hosting Guide](FREE_HOSTING_GUIDE.md) - Free hosting setup
- [Test Accounts](TEST_ACCOUNTS.md) - Test credentials

## 🧪 Testing

### Test Accounts

**Admin**
- Email: admin@worknpay.com
- Password: (set during setup)

**Paystack Test Card**
- Card: 4111 1111 1111 1111
- Expiry: Any future date
- CVV: Any 3 digits
- OTP: 123456

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Authors

- Your Name - Initial work

## 🙏 Acknowledgments

- Paystack for payment processing
- Ghana's informal sector workers
- All contributors and testers

## 📞 Support

For support, email support@worknpay.com or open an issue on GitHub.

## 🗺️ Roadmap

- [ ] Mobile app (iOS/Android)
- [ ] In-app chat system
- [ ] Advanced search filters
- [ ] Worker verification system
- [ ] Background checks integration
- [ ] SMS notifications
- [ ] Push notifications
- [ ] Multi-language support

## 📊 Project Status

✅ **Production Ready** - Version 1.0

All core features implemented and tested. Ready for deployment.

---

Made with ❤️ in Ghana

By Alan Kofi Safo Ofori
