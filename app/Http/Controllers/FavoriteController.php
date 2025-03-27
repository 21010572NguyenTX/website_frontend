<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    /**
     * Hiển thị danh sách bệnh đã bookmark của người dùng
     */
    public function favoriteDiseases()
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem danh sách bệnh đã lưu');
        }
        
        $user = Session::get('user');
        $userId = $user['id'];
        $token = Session::get('token');
        
        try {
            $response = Http::withToken($token)
                ->get("http://localhost:3000/api/favoritesDisease", [
                    'user_id' => $userId
                ]);
            
            $result = $response->json();
            
            if ($response->successful() && isset($result['success']) && $result['success']) {
                return view('favorites.diseases', [
                    'favorites' => $result['data'] ?? []
                ]);
            } else {
                return view('favorites.diseases', [
                    'favorites' => [],
                    'error' => $result['message'] ?? 'Không thể lấy danh sách bệnh đã lưu'
                ]);
            }
        } catch (\Exception $e) {
            return view('favorites.diseases', [
                'favorites' => [],
                'error' => 'Lỗi kết nối: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Hiển thị danh sách thuốc đã bookmark của người dùng
     */
    public function favoriteMedicines()
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem danh sách thuốc đã lưu');
        }
        
        $user = Session::get('user');
        $userId = $user['id'];
        $token = Session::get('token');
        
        try {
            $response = Http::withToken($token)
                ->get("http://localhost:3000/api/favoritesMedicine", [
                    'user_id' => $userId
                ]);
            
            $result = $response->json();
            
            if ($response->successful() && isset($result['success']) && $result['success']) {
                return view('favorites.medicines', [
                    'favorites' => $result['data'] ?? []
                ]);
            } else {
                return view('favorites.medicines', [
                    'favorites' => [],
                    'error' => $result['message'] ?? 'Không thể lấy danh sách thuốc đã lưu'
                ]);
            }
        } catch (\Exception $e) {
            return view('favorites.medicines', [
                'favorites' => [],
                'error' => 'Lỗi kết nối: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Thêm bệnh vào danh sách bookmark
     */
    public function addFavoriteDisease(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu bệnh'
            ], 401);
        }
        
        $validator = Validator::make($request->all(), [
            'disease_id' => 'required|integer',
            'note' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Session::get('user');
        $token = Session::get('token');
        
        try {
            // Sử dụng API proxy Laravel thay vì gọi trực tiếp
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $token
            ])->post('http://localhost:3000/api/favoritesDisease/add', [
                'user_id' => $user['id'],
                'disease_id' => $request->disease_id,
                'note' => $request->note ?? ''
            ]);
            
            $result = $response->json();
            
            // Ghi log để debug
            Log::debug('Kết quả thêm bệnh yêu thích:', [
                'request' => [
                    'user_id' => $user['id'],
                    'disease_id' => $request->disease_id,
                    'note' => $request->note ?? ''
                ],
                'response' => $result,
                'status' => $response->status()
            ]);
            
            return response()->json([
                'success' => $response->successful() && isset($result['success']) && $result['success'],
                'message' => $result['message'] ?? 'Không thể thêm bệnh vào danh sách đã lưu',
                'data' => $result['data'] ?? null
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm bệnh yêu thích:', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thêm thuốc vào danh sách bookmark
     */
    public function addFavoriteMedicine(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện chức năng này'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'medicine_id' => 'required|numeric',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Session::get('user');
        $token = Session::get('token');

        try {
            // Log dữ liệu request để debug
            Log::debug('Yêu cầu thêm thuốc yêu thích:', [
                'user_id' => $user['id'],
                'medicine_id' => $request->medicine_id,
                'note' => $request->note ?? ''
            ]);
            
            // Sửa URL từ tương đối sang tuyệt đối
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $token
            ])->post('http://localhost:3000/api/favoritesMedicine/add', [
                'user_id' => $user['id'],
                'medicine_id' => $request->medicine_id,
                'note' => $request->note ?? ''
            ]);

            $result = $response->json();
            
            // Ghi log để debug
            Log::debug('Kết quả thêm thuốc yêu thích:', [
                'request' => [
                    'user_id' => $user['id'],
                    'medicine_id' => $request->medicine_id,
                    'note' => $request->note ?? ''
                ],
                'response' => $result,
                'status' => $response->status()
            ]);

            return response()->json([
                'success' => $response->successful() && isset($result['success']) && $result['success'],
                'message' => $result['message'] ?? 'Không thể thêm thuốc vào danh sách đã lưu',
                'data' => $result['data'] ?? null
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm thuốc yêu thích:', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa bệnh khỏi danh sách bookmark
     */
    public function removeFavoriteDisease(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện'
            ]);
        }
        
        try {
            // Lấy dữ liệu
            $user = Session::get('user');
            $userId = $user['id'];
            
            // Dữ liệu có thể gửi qua query params hoặc form data
            $favoriteId = $request->input('favorite_id');
            if (!$favoriteId) {
                $jsonData = $request->json()->all();
                $favoriteId = $jsonData['favorite_id'] ?? null;
            }
            
            // Ghi log để debug
            \Log::debug('Dữ liệu xóa bệnh:', [
                'user_id' => $userId,
                'favorite_id' => $favoriteId,
                'request_all' => $request->all(),
                'request_json' => $request->json()->all()
            ]);
            
            // Kiểm tra favorite_id
            if (!$favoriteId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu ID bệnh cần xóa'
                ]);
            }
            
            // Chuẩn bị dữ liệu
            $data = [
                'user_id' => $userId,
                'favorite_id' => intval($favoriteId)
            ];
            
            // Gọi API
            $response = Http::post('http://localhost:3000/api/favoritesDisease/remove', $data);
            
            // Xử lý kết quả
            $result = $response->json();
            \Log::debug('Phản hồi API xóa bệnh:', $result);
            
            if ($result['success'] === true) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa bệnh khỏi danh sách đã lưu'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Không thể xóa bệnh'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi xóa bệnh:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Xóa thuốc khỏi danh sách bookmark
     */
    public function removeFavoriteMedicine(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện chức năng này'
            ], 401);
        }

        try {
            // Lấy dữ liệu
            $user = Session::get('user');
            $userId = $user['id'];
            $token = Session::get('token');
            
            // Dữ liệu có thể gửi qua query params hoặc form data
            $favoriteId = $request->input('favorite_id');
            if (!$favoriteId) {
                $jsonData = $request->json()->all();
                $favoriteId = $jsonData['favorite_id'] ?? null;
            }
            
            // Ghi log để debug
            \Log::debug('Dữ liệu xóa thuốc yêu thích:', [
                'user_id' => $userId,
                'favorite_id' => $favoriteId,
                'request_all' => $request->all(),
                'request_json' => $request->json()->all()
            ]);
            
            // Kiểm tra favorite_id
            if (!$favoriteId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu ID thuốc yêu thích cần xóa'
                ]);
            }
            
            // Chuẩn bị dữ liệu
            $data = [
                'user_id' => $userId,
                'favorite_id' => intval($favoriteId)
            ];
            
            // Gọi API - Thay đổi từ POST sang DELETE
            $response = Http::withToken($token)
                ->withBody(json_encode($data), 'application/json')
                ->delete('http://localhost:3000/api/favoritesMedicine/remove');
            
            // Xử lý kết quả
            $result = $response->json();
            \Log::debug('Phản hồi API xóa thuốc yêu thích:', $result);
            
            if ($result['success'] === true) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa thuốc yêu thích khỏi danh sách'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Không thể xóa thuốc yêu thích'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi xóa thuốc yêu thích:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cập nhật ghi chú cho bệnh đã bookmark
     */
    public function updateFavoriteDiseaseNote(Request $request)
    {
        \Log::info('Update disease note request:', $request->all());
        
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện'
            ]);
        }
        
        $validator = Validator::make($request->all(), [
            'favorite_id' => 'required|integer',
            'note' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ]);
        }
        
        $user = Session::get('user');
        $userId = $user['id'];
        $token = Session::get('token');
        
        try {
            // Lấy thông tin favorite để lấy disease_id
            $favoriteId = $request->favorite_id;

            // Sử dụng API để lấy thông tin favorite
            $getFavoriteResponse = Http::withToken($token)
                ->get("http://localhost:3000/api/favoritesDisease", [
                    'user_id' => $userId
                ]);
            
            $favorites = $getFavoriteResponse->json();
            
            if (!$getFavoriteResponse->successful() || !isset($favorites['success']) || !$favorites['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể lấy thông tin bệnh đã lưu'
                ]);
            }
            
            // Tìm favorite với id tương ứng
            $favorite = null;
            foreach ($favorites['data'] as $item) {
                if ($item['id'] == $favoriteId) {
                    $favorite = $item;
                    break;
                }
            }
            
            if (!$favorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy bệnh đã lưu với ID này'
                ]);
            }
            
            // Lấy disease_id từ favorite
            $diseaseId = $favorite['disease_id'] ?? $favorite['Disease']['id'] ?? null;
            
            \Log::info('Updating disease note with data:', [
                'user_id' => $userId,
                'favorite_id' => $favoriteId,
                'disease_id' => $diseaseId,
                'note' => $request->note
            ]);
            
            // Truy cập trực tiếp đến API với favorite_id
            $requestData = [
                'user_id' => $userId,
                'favorite_id' => $request->favorite_id,
                'disease_id' => $diseaseId, // Thêm disease_id
                'note' => $request->note
            ];
            
            \Log::info('Sending API request to update disease note:', $requestData);
            
            $response = Http::withToken($token)
                ->put('http://localhost:3000/api/favoritesDisease/update_note', $requestData);
            
            $result = $response->json();
            \Log::info('API response:', $result);
            
            if ($response->successful() && isset($result['success']) && $result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật thông tin thành công',
                    'data' => $result['data'] ?? null
                ]);
            } else {
                \Log::error('API error response:', $result);
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Không thể cập nhật thông tin'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Connection error:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cập nhật ghi chú của thuốc yêu thích
     */
    public function updateFavoriteMedicineNote(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để cập nhật ghi chú'
            ]);
        }
        
        $validator = Validator::make($request->all(), [
            'favorite_id' => 'required|integer',
            'note' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            Log::error('Validation failed for medicine note update:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ]);
        }
        
        $user = Session::get('user');
        $userId = $user['id'];
        $token = Session::get('token');
        
        try {
            // Lấy thông tin favorite để lấy medicine_id
            $favoriteId = $request->favorite_id;

            // Sử dụng API để lấy thông tin favorite
            $getFavoriteResponse = Http::withToken($token)
                ->get("http://localhost:3000/api/favoritesMedicine", [
                    'user_id' => $userId
                ]);
            
            $favorites = $getFavoriteResponse->json();
            
            if (!$getFavoriteResponse->successful() || !isset($favorites['success']) || !$favorites['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể lấy thông tin thuốc yêu thích'
                ]);
            }
            
            // Tìm favorite với id tương ứng
            $favorite = null;
            foreach ($favorites['data'] as $item) {
                if ($item['id'] == $favoriteId) {
                    $favorite = $item;
                    break;
                }
            }
            
            if (!$favorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thuốc yêu thích với ID này'
                ]);
            }
            
            // Lấy medicine_id từ favorite
            $medicineId = $favorite['medicine_id'] ?? $favorite['Medicine']['id'] ?? null;
            
            Log::info('Updating medicine note with data:', [
                'user_id' => $userId,
                'favorite_id' => $favoriteId,
                'medicine_id' => $medicineId,
                'note' => $request->note
            ]);
            
            // Truy cập trực tiếp đến API với favorite_id
            $requestData = [
                'user_id' => $userId,
                'favorite_id' => $request->favorite_id,
                'medicine_id' => $medicineId, // Thêm medicine_id
                'note' => $request->note
            ];
            
            Log::info('Sending update request to API:', $requestData);
            
            // Sử dụng PUT để gọi đến API, mặc dù nhận POST từ frontend
            $response = Http::withToken($token)
                ->put('http://localhost:3000/api/favoritesMedicine/update_note', $requestData);
            
            $result = $response->json();
            Log::info('API response for medicine note update:', $result);
            
            if ($response->successful() && isset($result['success']) && $result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật thông tin thành công',
                    'data' => $result['data'] ?? null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Không thể cập nhật thông tin'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ]);
        }
    }
} 