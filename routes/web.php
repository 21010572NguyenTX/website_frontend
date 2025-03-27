<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiDataController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ApiDataController::class, 'home'])->name('home');

// Routes xác thực - đặt ở đầu để ưu tiên cao
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('update-profile');
Route::post('/profile/change-password', [AuthController::class, 'changePassword'])->name('change-password');

// Xử lý API admin - ưu tiên trước các route có pattern
Route::prefix('admin')->group(function () {
    // Quản lý thuốc và bệnh - route cố định
    Route::get('/medicines', [AdminController::class, 'medicinesIndex'])->name('admin.medicines');
    Route::get('/diseases', [AdminController::class, 'diseasesIndex'])->name('admin.diseases');
    Route::get('/users', [AdminController::class, 'usersIndex'])->name('admin.users');
});

// Routes bookmark
Route::get('/favorite-medicines', [FavoriteController::class, 'favoriteMedicines'])->name('favorite-medicines');
Route::get('/favorite-diseases', [FavoriteController::class, 'favoriteDiseases'])->name('favorite-diseases');

Route::post('/add-favorite-medicine', [FavoriteController::class, 'addFavoriteMedicine'])->name('add-favorite-medicine');
Route::post('/add-favorite-disease', [FavoriteController::class, 'addFavoriteDisease'])->name('add-favorite-disease');

Route::match(['delete', 'post'], '/remove-favorite-medicine', [FavoriteController::class, 'removeFavoriteMedicine'])->name('remove-favorite-medicine');
Route::post('/remove-favorite-disease', [FavoriteController::class, 'removeFavoriteDisease'])->name('remove-favorite-disease');

Route::post('/update-favorite-medicine-note', [FavoriteController::class, 'updateFavoriteMedicineNote'])->name('update-favorite-medicine-note');
Route::post('/update-favorite-disease-note', [FavoriteController::class, 'updateFavoriteDiseaseNote'])->name('update-favorite-disease-note');

// Routes thuốc và bệnh với pattern {id} - đặt sau cùng để tránh match các route khác
Route::get('/medicines', [ApiDataController::class, 'medicines'])->name('medicines');
Route::get('/medicines/{id}', [ApiDataController::class, 'medicineDetail'])->name('medicines.show');

Route::get('/diseases', [ApiDataController::class, 'diseases'])->name('diseases');
Route::get('/diseases/{id}', [ApiDataController::class, 'diseaseDetail'])->name('diseases.show');

// Thêm các routes API cho việc lấy dữ liệu
Route::prefix('api')->group(function () {
    Route::get('/medicines/{id}', [ApiController::class, 'getMedicineById']);
    Route::get('/diseases/{id}', [ApiController::class, 'getDiseaseById']);
});