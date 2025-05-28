# SEFRUIT DOKUMENTASI PROJECT GMEDIA INTEGRASI SENTRY DAN OPEN PROJECT

## Run Project Backend API:
1. Buka direktori `backend-api`:
    ```bash
    cd backend-api
    ```
2. Install dependensi Composer:
    ```bash
    composer install
    ```
3. Jalankan migrasi dan seed database:
    ```bash
    php artisan migrate
    php artisan db:seed
4. Jalankan update project jobs, tunggu sampai selesai:
    ```bash
    php artisan update:project
    ```
5. Jalankan server Laravel:
    ```bash
    php artisan serve 
    ```
6. Jalankan Jobs Laravel di Terminal lain:
    ```bash
    php artisan queue:work 
    ```

## Run Project Frontend API:
1. Buka direktori `vue`:
    ```bash
    cd vue
    ```
2. Update dependensi Composer:
    ```bash
    npm install
    ```
5. Jalankan server Vue JS:
    ```bash
    npm run dev
    ```
