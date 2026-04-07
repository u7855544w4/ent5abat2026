# Election Monitoring System 2026 - Laravel

نظام متابعة ميدان انتخبات البلدية 2026 مبني بلغة PHP باستخدام Laravel framework.

## المتطلبات

- PHP 8.1+
- Composer
- SQLite أو MySQL

## التثبيت

```bash
# Install dependencies
composer install

# Create database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Start server
php artisan serve
```

## الصفحات

- `/` - الصفحة الرئيسية (Dashboard)
- `/voters` - الناخبين
- `/families` - العائلات
- `/committees` - اللجان
- `/tasks` - توزيع المهام
- `/reports` - التقارير
- `/import` - استيراد من Excel

## المميزات

- استيراد الناخبين من ملف Excel
- فلترة حسب العائلة والمركز انتخابي والحالة
- توزيع الناخبين على اللجان
- تقارير مفصلة
- واجهة عربية RTL

## الترخيص

MIT