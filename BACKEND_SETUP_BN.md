# ViperPro Laravel Backend Setup

এই package-টা original Laravel backend থেকে আলাদা করে তৈরি করা হয়েছে।

## কী আছে
- Laravel 10 backend
- API routes
- JWT auth
- Wallet / Deposit / Withdraw logic
- Game / Provider routes
- Stripe / SuitPay / BsPay gateway routes
- Filament admin panel

## চালানোর জন্য কী লাগবে
- PHP 8.1+
- Composer
- MySQL বা MariaDB
- Node.js (assets build করার জন্য)

## basic setup
1. project folder open করুন
2. `.env` file তৈরি করুন
3. composer install চালান
4. php artisan key:generate চালান
5. database credentials দিন
6. php artisan serve চালান

## গুরুত্বপূর্ণ warning
এই source-এ database migrations পাওয়া যায়নি।
তাই এটা full turnkey backend না।
Models, controllers, routes আছে; কিন্তু clean database schema আপনাকে rebuild বা extract করতে হবে।

## Admin
README অনুযায়ী default admin path `/admin`
Default credentials source-এ hardcoded example হিসেবে আছে, production-এ অবশ্যই বদলাতে হবে।

## Security
Production-এ deploy করার আগে অন্তত এগুলো ঠিক করুন:
- public `/clear` route বন্ধ করুন
- default admin credentials বদলান
- provider/payment secrets `.env`-এ নিন
- exposed frontend keys remove করুন
- CSRF/webhook policy review করুন
