<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Proxy API cho thuốc
Route::get('/medicines', function() {
    $response = Http::get('http://localhost:3000/api/medicines');
    return $response->json();
});

Route::get('/medicines/paginated', function(Request $request) {
    $response = Http::get('http://localhost:3000/api/medicines/paginated', $request->all());
    return $response->json();
});

Route::get('/medicines/{id}', function($id) {
    $response = Http::get('http://localhost:3000/api/medicines/' . $id);
    return $response->json();
});

Route::post('/medicines/add', function(Request $request) {
    $response = Http::post('http://localhost:3000/api/medicines/add', $request->all());
    return $response->json();
});

Route::put('/medicines/update', function(Request $request) {
    $response = Http::put('http://localhost:3000/api/medicines/update', $request->all());
    return $response->json();
});

Route::post('/medicines/delete', function(Request $request) {
    $response = Http::post('http://localhost:3000/api/medicines/delete', $request->all());
    return $response->json();
});

// Đừng xóa route cũ để đảm bảo tương thích ngược
Route::delete('/medicines/delete', function(Request $request) {
    $response = Http::post('http://localhost:3000/api/medicines/delete', $request->all());
    return $response->json();
});

Route::post('/medicines/import', function(Request $request) {
    $response = Http::attach('csv', $request->file('csv'), 'file.csv')
        ->post('http://localhost:3000/api/medicines/import');
    return $response->json();
});

// Proxy API cho bệnh
Route::get('/diseases', function() {
    $response = Http::get('http://localhost:3000/api/diseases');
    return $response->json();
});

Route::get('/diseases/paginated', function(Request $request) {
    $response = Http::get('http://localhost:3000/api/diseases/paginated', $request->all());
    return $response->json();
});

Route::get('/diseases/{id}', function($id) {
    $response = Http::get('http://localhost:3000/api/diseases/' . $id);
    return $response->json();
});

Route::post('/diseases/add', function(Request $request) {
    $response = Http::post('http://localhost:3000/api/diseases/add', $request->all());
    return $response->json();
});

Route::put('/diseases/update', function(Request $request) {
    $response = Http::put('http://localhost:3000/api/diseases/update', $request->all());
    return $response->json();
});

Route::delete('/diseases/delete', function(Request $request) {
    $response = Http::delete('http://localhost:3000/api/diseases/delete', $request->all());
    return $response->json();
});

Route::post('/diseases/import', function(Request $request) {
    $response = Http::attach('csv', $request->file('csv'), 'file.csv')
        ->post('http://localhost:3000/api/diseases/import');
    return $response->json();
});

// Proxy API cho người dùng
Route::get('/users', function(Request $request) {
    $response = Http::get('http://localhost:3000/api/users', $request->all());
    return $response->json();
});

Route::get('/users/{id}', function($id) {
    $response = Http::get('http://localhost:3000/api/users/' . $id);
    return $response->json();
});

Route::post('/users/add', function(Request $request) {
    $response = Http::post('http://localhost:3000/api/users/add', $request->all());
    return $response->json();
});

Route::put('/users/update', function(Request $request) {
    $response = Http::put('http://localhost:3000/api/users/update', $request->all());
    return $response->json();
});

Route::delete('/users/delete', function(Request $request) {
    $response = Http::delete('http://localhost:3000/api/users/delete', $request->all());
    return $response->json();
});

// API endpoint cho cập nhật thông tin cá nhân
Route::put('/users/{id}/profile', function (Request $request, $id) {
    $apiUrl = env('API_URL', 'http://localhost:3000');
    
    try {
        $response = Http::put($apiUrl . '/api/users/' . $id . '/profile', $request->all());
        
        return $response->json();
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không thể kết nối đến API: ' . $e->getMessage()
        ], 500);
    }
});

// API endpoint cho đổi mật khẩu
Route::post('/users/{id}/change-password', function (Request $request, $id) {
    $apiUrl = env('API_URL', 'http://localhost:3000');
    
    try {
        $response = Http::post($apiUrl . '/api/users/' . $id . '/change-password', [
            'currentPassword' => $request->input('currentPassword'),
            'newPassword' => $request->input('newPassword')
        ]);
        
        return $response->json();
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không thể kết nối đến API: ' . $e->getMessage()
        ], 500);
    }
});

// Proxy API cho danh sách thuốc yêu thích
Route::post('/favoritesMedicine/add', function(Request $request) {
    $response = Http::post('http://localhost:3000/api/favoritesMedicine/add', $request->all());
    return $response->json();
});

Route::get('/favoritesMedicine', function(Request $request) {
    $response = Http::get('http://localhost:3000/api/favoritesMedicine', $request->query());
    return $response->json();
});

Route::post('/favoritesMedicine/remove', function(Request $request) {
    $response = Http::post('http://localhost:3000/api/favoritesMedicine/remove', $request->all());
    return $response->json();
});

Route::put('/favoritesMedicine/update_note', function(Request $request) {
    $response = Http::put('http://localhost:3000/api/favoritesMedicine/update_note', $request->all());
    return $response->json();
});

// Proxy API cho danh sách bệnh yêu thích
Route::post('/favoritesDisease/add', function(Request $request) {
    $response = Http::post('http://localhost:3000/api/favoritesDisease/add', $request->all());
    return $response->json();
});

Route::get('/favoritesDisease', function(Request $request) {
    $response = Http::get('http://localhost:3000/api/favoritesDisease', $request->query());
    return $response->json();
});

Route::post('/favoritesDisease/remove', function(Request $request) {
    $response = Http::post('http://localhost:3000/api/favoritesDisease/remove', $request->all());
    return $response->json();
});

Route::put('/favoritesDisease/update_note', function(Request $request) {
    $response = Http::put('http://localhost:3000/api/favoritesDisease/update_note', $request->all());
    return $response->json();
});
