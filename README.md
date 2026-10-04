# نظام مكتب رامي للمحاماة

| المجلد | المحتوى | يُنشر إلى (demo / الأساسي) |
|---|---|---|
| `backend/` | الـ API (Laravel 8) مع إصلاحات المرحلة 1 و 2 | `demo-back` / `back` |
| `legacy-web/` | الواجهة الحالية (مبنية، بلا كود مصدري) مع ترقيعاتها | `demo` / `web` |
| `web/` | الواجهة الجديدة، تُبنى شاشة بعد شاشة على نفس الـ API | `app-demo` / (لاحقًا) |

لا يحتوي المستودع على: `.env`، `vendor/`، `storage/` (ومنه مفاتيح Passport)، `images/` (ملفات العملاء). هذه تبقى على السيرفر فقط ولا يلمسها النشر.

## الواجهة الجديدة

## الشاشات
- [x] تسجيل الدخول (نفس حسابات النظام)
- [x] **يومي**: جلسات اليوم، المهام المتأخرة، السندات المستحقة
- [ ] بحث شامل (Ctrl+K)
- [ ] إضافة سريعة لعميل/تواصل
- [ ] ملف العميل الموحّد

## التشغيل محليًا
```bash
cd web
cp .env.example .env   # عدّل عنوان الـ API عند الحاجة
npm install
npm run dev
```

## النشر (آلي)
- **demo**: كل دفع إلى `main` ينشر تلقائيًا الأجزاء التي تغيّرت فقط.
- **الأساسي**: يدويًا فقط من GitHub: Actions ← Deploy ← Run workflow ← `target = production`.

الإعدادات في GitHub ← Settings ← Environments، بيئتان `demo` و `production`، في كل منهما:

| النوع | الاسم | مثال |
|---|---|---|
| Secret | `FTP_SERVER` | عنوان FTP من hPanel |
| Secret | `FTP_USERNAME` | |
| Secret | `FTP_PASSWORD` | |
| Variable | `BACKEND_DIR` | `domains/demo-back.ramilawyersys.com/public_html/` |
| Variable | `LEGACY_WEB_DIR` | `domains/demo.ramilawyersys.com/public_html/` |
| Variable | `APP_DIR` | `domains/app-demo.ramilawyersys.com/public_html/` |
| Variable (اختياري) | `FTP_PROTOCOL` | `ftps` (الافتراضي) أو `ftp` |

أي مجلد لم يُضبط يُتخطّى نشره بتحذير بدل الفشل. المسارات تنتهي بـ `/` وتكون نسبةً إلى جذر حساب FTP.

نسخة demo من الواجهة القديمة تُولَّد أثناء النشر: يُستبدل عنوان الـ API بـ `demo-back` وتُضاف شارة DEMO.
