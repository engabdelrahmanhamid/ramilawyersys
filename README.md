# واجهة مكتب رامي للمحاماة (الجديدة)

واجهة جديدة تُبنى شاشة بعد شاشة على نفس الـ API الحالي (`/apiAdmin`)، وتعمل بجانب الواجهة القديمة حتى تستبدلها.

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

## البناء والنشر على Hostinger
```bash
cd web && npm run build
```
ارفع محتوى `web/dist/` (يشمل `.htaccess`) إلى جذر النطاق الفرعي.

| المتغير | demo | الأساسي |
|---|---|---|
| `VITE_API_BASE` | `https://demo-back.ramilawyersys.com/apiAdmin` | `https://back.ramilawyersys.com/apiAdmin` |
| `VITE_OLD_APP` | `https://demo.ramilawyersys.com` | `https://web.ramilawyersys.com` |
| `VITE_DEMO` | `true` | `false` |
