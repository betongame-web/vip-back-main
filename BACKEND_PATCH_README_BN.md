এই patch zip extract করে vip-back-main structure merge/replace করবেন.

এই patch-এ আছে:
- Render boot/stability fix
- root and api root JSON response fix
- static health check fix
- settings/banner/category/search/game fallback API fix

Replace করার file list:
- Dockerfile
- start.sh
- server.php
- public/healthz.txt
- routes/web.php
- routes/api.php
- app/Http/Controllers/Layouts/ApplicationController.php
- app/Http/Controllers/Api/Settings/SettingController.php
- app/Http/Controllers/Api/Settings/BannerController.php
- app/Http/Controllers/Api/Categories/CategoryController.php
- app/Http/Controllers/Api/Search/SearchGameController.php
- app/Http/Controllers/Api/Games/GameController.php

তারপর GitHub-এ push করে Render-এ Manual Deploy দিন.
