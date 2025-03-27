<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Hiển thị trang quản lý thuốc
     */
    public function medicinesIndex()
    {
        return view('admin.medicines');
    }
    
    /**
     * Hiển thị trang quản lý bệnh
     */
    public function diseasesIndex()
    {
        return view('admin.diseases');
    }
    
    /**
     * Hiển thị trang quản lý người dùng
     */
    public function usersIndex()
    {
        return view('admin.users');
    }
    
    /**
     * Thêm thuốc mới
     */
    public function addMedicine(Request $request)
    {
        // Xác minh rằng request là AJAX
        if (!$request->ajax() && !$request->wantsJson()) {
            \Log::warning('Yêu cầu không phải AJAX hoặc không chấp nhận JSON');
            return response()->json([
                'success' => false, 
                'message' => 'Yêu cầu không hợp lệ'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'ten_thuoc' => 'required|string|max:255',
            'thanh_phan' => 'nullable|string',
            'cong_dung' => 'nullable|string',
            'tac_dung_phu' => 'nullable|string',
            'hinh_anh' => 'nullable|url',
            'nha_san_xuat' => 'nullable|string',
            'danh_gia_tot' => 'nullable|integer|min:0',
            'danh_gia_trung_binh' => 'nullable|integer|min:0',
            'danh_gia_kem' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Lấy và kiểm tra token
        $token = Session::get('token');
        
        \Log::info('Session ID: ' . Session::getId());
        \Log::info('Session data các trường: ' . json_encode(array_keys(Session::all())));
        
        if (!$token) {
            \Log::error('Không tìm thấy token trong session');
            \Log::error('Session Data: ' . json_encode(Session::all()));
            
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc hết hạn, vui lòng đăng nhập lại',
                'redirect' => route('login')
            ], 401);
        }
        
        // Chuẩn bị dữ liệu
        $dataSend = $request->all();
        // Đảm bảo các trường null được thay thế bằng 'không rõ'
        $dataSend['thanh_phan'] = $request->input('thanh_phan') ?: 'không rõ';
        $dataSend['cong_dung'] = $request->input('cong_dung') ?: 'không rõ';
        $dataSend['tac_dung_phu'] = $request->input('tac_dung_phu') ?: 'không rõ';
        $dataSend['nha_san_xuat'] = $request->input('nha_san_xuat') ?: 'không rõ';
        $dataSend['danh_gia_tot'] = is_numeric($request->input('danh_gia_tot')) ? $request->input('danh_gia_tot') : 0;
        $dataSend['danh_gia_trung_binh'] = is_numeric($request->input('danh_gia_trung_binh')) ? $request->input('danh_gia_trung_binh') : 0;
        $dataSend['danh_gia_kem'] = is_numeric($request->input('danh_gia_kem')) ? $request->input('danh_gia_kem') : 0;
        
        \Log::info('Dữ liệu gửi đi: ' . json_encode($dataSend));

        try {
            $apiUrl = '/api/medicines/add';
            \Log::info('Gửi yêu cầu đến: ' . $apiUrl);
            \Log::info('Token sử dụng: ' . substr($token, 0, 20) . '...');
            
            // Thiết lập headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $token
            ];
            
            \Log::info('Headers gửi đi: ' . json_encode($headers));
            
            $response = Http::withHeaders($headers)->post($apiUrl, $dataSend);
            
            \Log::info('Status code: ' . $response->status());
            \Log::info('Phản hồi từ API: ' . $response->body());
            
            if ($response->status() === 302) {
                \Log::warning('Bị chuyển hướng 302 đến: ' . $response->header('Location'));
                return response()->json([
                    'success' => false,
                    'message' => 'Bị chuyển hướng, có thể do vấn đề xác thực',
                    'redirect' => $response->header('Location') ?: route('login')
                ], 401);
            }
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ API: ' . $response->status(),
                    'details' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi kết nối API: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cập nhật thông tin thuốc
     */
    public function updateMedicine(Request $request)
    {
        // Xác minh rằng request là AJAX
        if (!$request->ajax() && !$request->wantsJson()) {
            \Log::warning('Yêu cầu không phải AJAX hoặc không chấp nhận JSON');
            return response()->json([
                'success' => false, 
                'message' => 'Yêu cầu không hợp lệ'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'ten_thuoc' => 'required|string|max:255',
            'thanh_phan' => 'nullable|string',
            'cong_dung' => 'nullable|string',
            'tac_dung_phu' => 'nullable|string',
            'hinh_anh' => 'nullable|url',
            'nha_san_xuat' => 'nullable|string',
            'danh_gia_tot' => 'nullable|integer|min:0',
            'danh_gia_trung_binh' => 'nullable|integer|min:0',
            'danh_gia_kem' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Lấy và kiểm tra token
        $token = Session::get('token');
        
        \Log::info('Session ID: ' . Session::getId());
        \Log::info('Session data các trường: ' . json_encode(array_keys(Session::all())));
        
        if (!$token) {
            \Log::error('Không tìm thấy token trong session');
            \Log::error('Session Data: ' . json_encode(Session::all()));
            
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc hết hạn, vui lòng đăng nhập lại',
                'redirect' => route('login')
            ], 401);
        }
        
        // Chuẩn bị dữ liệu
        $dataSend = $request->all();
        // Đảm bảo các trường null được thay thế bằng 'không rõ'
        $dataSend['thanh_phan'] = $request->input('thanh_phan') ?: 'không rõ';
        $dataSend['cong_dung'] = $request->input('cong_dung') ?: 'không rõ';
        $dataSend['tac_dung_phu'] = $request->input('tac_dung_phu') ?: 'không rõ';
        $dataSend['nha_san_xuat'] = $request->input('nha_san_xuat') ?: 'không rõ';
        $dataSend['danh_gia_tot'] = is_numeric($request->input('danh_gia_tot')) ? $request->input('danh_gia_tot') : 0;
        $dataSend['danh_gia_trung_binh'] = is_numeric($request->input('danh_gia_trung_binh')) ? $request->input('danh_gia_trung_binh') : 0;
        $dataSend['danh_gia_kem'] = is_numeric($request->input('danh_gia_kem')) ? $request->input('danh_gia_kem') : 0;
        
        \Log::info('Dữ liệu gửi đi: ' . json_encode($dataSend));

        try {
            $apiUrl = '/api/medicines/update';
            \Log::info('Gửi yêu cầu cập nhật thuốc đến: ' . $apiUrl);
            \Log::info('Token sử dụng: ' . substr($token, 0, 20) . '...');
            
            // Thiết lập headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $token
            ];
            
            \Log::info('Headers gửi đi: ' . json_encode($headers));
            
            $response = Http::withHeaders($headers)->put($apiUrl, $dataSend);
            
            \Log::info('Status code: ' . $response->status());
            \Log::info('Phản hồi từ API: ' . $response->body());
            
            if ($response->status() === 302) {
                \Log::warning('Bị chuyển hướng 302 đến: ' . $response->header('Location'));
                return response()->json([
                    'success' => false,
                    'message' => 'Bị chuyển hướng, có thể do vấn đề xác thực',
                    'redirect' => $response->header('Location') ?: route('login')
                ], 401);
            }
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ API: ' . $response->status(),
                    'details' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi kết nối API: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Xóa thuốc
     */
    public function deleteMedicine(Request $request)
    {
        // Xác minh rằng request là AJAX
        if (!$request->ajax() && !$request->wantsJson()) {
            \Log::warning('Yêu cầu không phải AJAX hoặc không chấp nhận JSON');
            return response()->json([
                'success' => false, 
                'message' => 'Yêu cầu không hợp lệ'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu ID thuốc cần xóa',
                'errors' => $validator->errors()
            ], 422);
        }

        // Lấy và kiểm tra token
        $token = Session::get('token');
        
        \Log::info('Session ID: ' . Session::getId());
        \Log::info('Session data các trường: ' . json_encode(array_keys(Session::all())));
        
        if (!$token) {
            \Log::error('Không tìm thấy token trong session');
            \Log::error('Session Data: ' . json_encode(Session::all()));
            
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc hết hạn, vui lòng đăng nhập lại',
                'redirect' => route('login')
            ], 401);
        }

        try {
            $apiUrl = '/api/medicines/delete';
            \Log::info('Gửi yêu cầu xóa thuốc đến: ' . $apiUrl);
            \Log::info('ID thuốc cần xóa: ' . $request->id);
            \Log::info('Token sử dụng: ' . substr($token, 0, 20) . '...');
            
            // Thiết lập headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $token
            ];
            
            \Log::info('Headers gửi đi: ' . json_encode($headers));
            
            $response = Http::withHeaders($headers)->delete($apiUrl, ['id' => $request->id]);
            
            \Log::info('Status code: ' . $response->status());
            \Log::info('Phản hồi từ API: ' . $response->body());
            
            if ($response->status() === 302) {
                \Log::warning('Bị chuyển hướng 302 đến: ' . $response->header('Location'));
                return response()->json([
                    'success' => false,
                    'message' => 'Bị chuyển hướng, có thể do vấn đề xác thực',
                    'redirect' => $response->header('Location') ?: route('login')
                ], 401);
            }
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ API: ' . $response->status(),
                    'details' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi kết nối API: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Import danh sách thuốc từ file CSV
     */
    public function importMedicines(Request $request)
    {
        // Xác minh rằng request là AJAX hoặc multipart form
        if (!$request->ajax() && !$request->hasFile('file')) {
            \Log::warning('Yêu cầu không phải AJAX hoặc không có file');
            return response()->json([
                'success' => false, 
                'message' => 'Yêu cầu không hợp lệ'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Lấy và kiểm tra token
        $token = Session::get('token');
        
        \Log::info('Session ID: ' . Session::getId());
        \Log::info('Session data các trường: ' . json_encode(array_keys(Session::all())));
        
        if (!$token) {
            \Log::error('Không tìm thấy token trong session');
            \Log::error('Session Data: ' . json_encode(Session::all()));
            
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc hết hạn, vui lòng đăng nhập lại',
                'redirect' => route('login')
            ], 401);
        }
        
        $file = $request->file('file');
        \Log::info('Gửi yêu cầu import file thuốc: ' . $file->getClientOriginalName());

        try {
            $apiUrl = '/api/medicines/import';
            \Log::info('Gửi yêu cầu đến: ' . $apiUrl);
            \Log::info('Token sử dụng: ' . substr($token, 0, 20) . '...');
            
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $token
            ])->attach(
                'file', 
                file_get_contents($file), 
                $file->getClientOriginalName()
            )->post($apiUrl);
            
            \Log::info('Status code: ' . $response->status());
            \Log::info('Phản hồi từ API: ' . $response->body());
            
            if ($response->status() === 302) {
                \Log::warning('Bị chuyển hướng 302 đến: ' . $response->header('Location'));
                return response()->json([
                    'success' => false,
                    'message' => 'Bị chuyển hướng, có thể do vấn đề xác thực',
                    'redirect' => $response->header('Location') ?: route('login')
                ], 401);
            }
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ API: ' . $response->status(),
                    'details' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi kết nối API: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Thêm bệnh mới
     */
    public function addDisease(Request $request)
    {
        // Xác minh rằng request là AJAX
        if (!$request->ajax() && !$request->wantsJson()) {
            \Log::warning('Yêu cầu không phải AJAX hoặc không chấp nhận JSON');
            return response()->json([
                'success' => false, 
                'message' => 'Yêu cầu không hợp lệ'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'ten_benh' => 'required|string|max:255',
            'dinh_nghia' => 'nullable|string',
            'nguyen_nhan' => 'nullable|string',
            'trieu_chung' => 'nullable|string',
            'chan_doan' => 'nullable|string',
            'dieu_tri' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Lấy và kiểm tra token
        $token = Session::get('token');
        
        \Log::info('Session ID: ' . Session::getId());
        \Log::info('Session data các trường: ' . json_encode(array_keys(Session::all())));
        
        if (!$token) {
            \Log::error('Không tìm thấy token trong session');
            \Log::error('Session Data: ' . json_encode(Session::all()));
            
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc hết hạn, vui lòng đăng nhập lại',
                'redirect' => route('login')
            ], 401);
        }
        
        // Chuẩn bị dữ liệu
        $dataSend = $request->all();
        // Đảm bảo các trường null được thay thế bằng 'không rõ'
        $dataSend['dinh_nghia'] = $request->input('dinh_nghia') ?: 'không rõ';
        $dataSend['nguyen_nhan'] = $request->input('nguyen_nhan') ?: 'không rõ';
        $dataSend['trieu_chung'] = $request->input('trieu_chung') ?: 'không rõ';
        $dataSend['chan_doan'] = $request->input('chan_doan') ?: 'không rõ';
        $dataSend['dieu_tri'] = $request->input('dieu_tri') ?: 'không rõ';
        
        \Log::info('Dữ liệu bệnh gửi đi: ' . json_encode($dataSend));

        try {
            $apiUrl = '/api/diseases/add';
            \Log::info('Gửi yêu cầu đến: ' . $apiUrl);
            \Log::info('Token sử dụng: ' . substr($token, 0, 20) . '...');
            
            // Thiết lập headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $token
            ];
            
            \Log::info('Headers gửi đi: ' . json_encode($headers));
            
            $response = Http::withHeaders($headers)->post($apiUrl, $dataSend);
            
            \Log::info('Status code: ' . $response->status());
            \Log::info('Phản hồi từ API: ' . $response->body());
            
            if ($response->status() === 302) {
                \Log::warning('Bị chuyển hướng 302 đến: ' . $response->header('Location'));
                return response()->json([
                    'success' => false,
                    'message' => 'Bị chuyển hướng, có thể do vấn đề xác thực',
                    'redirect' => $response->header('Location') ?: route('login')
                ], 401);
            }
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ API: ' . $response->status(),
                    'details' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi kết nối API: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cập nhật thông tin bệnh
     */
    public function updateDisease(Request $request)
    {
        // Xác minh rằng request là AJAX
        if (!$request->ajax() && !$request->wantsJson()) {
            \Log::warning('Yêu cầu không phải AJAX hoặc không chấp nhận JSON');
            return response()->json([
                'success' => false, 
                'message' => 'Yêu cầu không hợp lệ'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'ten_benh' => 'required|string|max:255',
            'dinh_nghia' => 'nullable|string',
            'nguyen_nhan' => 'nullable|string',
            'trieu_chung' => 'nullable|string',
            'chan_doan' => 'nullable|string',
            'dieu_tri' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Lấy và kiểm tra token
        $token = Session::get('token');
        
        \Log::info('Session ID: ' . Session::getId());
        \Log::info('Session data các trường: ' . json_encode(array_keys(Session::all())));
        
        if (!$token) {
            \Log::error('Không tìm thấy token trong session');
            \Log::error('Session Data: ' . json_encode(Session::all()));
            
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc hết hạn, vui lòng đăng nhập lại',
                'redirect' => route('login')
            ], 401);
        }
        
        // Chuẩn bị dữ liệu
        $dataSend = $request->all();
        // Đảm bảo các trường null được thay thế bằng 'không rõ'
        $dataSend['dinh_nghia'] = $request->input('dinh_nghia') ?: 'không rõ';
        $dataSend['nguyen_nhan'] = $request->input('nguyen_nhan') ?: 'không rõ';
        $dataSend['trieu_chung'] = $request->input('trieu_chung') ?: 'không rõ';
        $dataSend['chan_doan'] = $request->input('chan_doan') ?: 'không rõ';
        $dataSend['dieu_tri'] = $request->input('dieu_tri') ?: 'không rõ';
        
        \Log::info('Dữ liệu bệnh gửi đi: ' . json_encode($dataSend));

        try {
            $apiUrl = '/api/diseases/update';
            \Log::info('Gửi yêu cầu cập nhật bệnh đến: ' . $apiUrl);
            \Log::info('Token sử dụng: ' . substr($token, 0, 20) . '...');
            
            // Thiết lập headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $token
            ];
            
            \Log::info('Headers gửi đi: ' . json_encode($headers));
            
            $response = Http::withHeaders($headers)->put($apiUrl, $dataSend);
            
            \Log::info('Status code: ' . $response->status());
            \Log::info('Phản hồi từ API: ' . $response->body());
            
            if ($response->status() === 302) {
                \Log::warning('Bị chuyển hướng 302 đến: ' . $response->header('Location'));
                return response()->json([
                    'success' => false,
                    'message' => 'Bị chuyển hướng, có thể do vấn đề xác thực',
                    'redirect' => $response->header('Location') ?: route('login')
                ], 401);
            }
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ API: ' . $response->status(),
                    'details' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi kết nối API: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Xóa bệnh
     */
    public function deleteDisease(Request $request)
    {
        // Xác minh rằng request là AJAX
        if (!$request->ajax() && !$request->wantsJson()) {
            \Log::warning('Yêu cầu không phải AJAX hoặc không chấp nhận JSON');
            return response()->json([
                'success' => false, 
                'message' => 'Yêu cầu không hợp lệ'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu ID bệnh cần xóa',
                'errors' => $validator->errors()
            ], 422);
        }

        // Lấy và kiểm tra token
        $token = Session::get('token');
        
        \Log::info('Session ID: ' . Session::getId());
        \Log::info('Session data các trường: ' . json_encode(array_keys(Session::all())));
        
        if (!$token) {
            \Log::error('Không tìm thấy token trong session');
            \Log::error('Session Data: ' . json_encode(Session::all()));
            
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc hết hạn, vui lòng đăng nhập lại',
                'redirect' => route('login')
            ], 401);
        }

        try {
            $apiUrl = '/api/diseases/delete';
            \Log::info('Gửi yêu cầu xóa bệnh đến: ' . $apiUrl);
            \Log::info('ID bệnh cần xóa: ' . $request->id);
            \Log::info('Token sử dụng: ' . substr($token, 0, 20) . '...');
            
            // Thiết lập headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $token
            ];
            
            \Log::info('Headers gửi đi: ' . json_encode($headers));
            
            $response = Http::withHeaders($headers)->delete($apiUrl, ['id' => $request->id]);
            
            \Log::info('Status code: ' . $response->status());
            \Log::info('Phản hồi từ API: ' . $response->body());
            
            if ($response->status() === 302) {
                \Log::warning('Bị chuyển hướng 302 đến: ' . $response->header('Location'));
                return response()->json([
                    'success' => false,
                    'message' => 'Bị chuyển hướng, có thể do vấn đề xác thực',
                    'redirect' => $response->header('Location') ?: route('login')
                ], 401);
            }
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ API: ' . $response->status(),
                    'details' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi kết nối API: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Import danh sách bệnh từ file CSV
     */
    public function importDiseases(Request $request)
    {
        // Xác minh rằng request là AJAX hoặc multipart form
        if (!$request->ajax() && !$request->hasFile('file')) {
            \Log::warning('Yêu cầu không phải AJAX hoặc không có file');
            return response()->json([
                'success' => false, 
                'message' => 'Yêu cầu không hợp lệ'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Lấy và kiểm tra token
        $token = Session::get('token');
        
        \Log::info('Session ID: ' . Session::getId());
        \Log::info('Session data các trường: ' . json_encode(array_keys(Session::all())));
        
        if (!$token) {
            \Log::error('Không tìm thấy token trong session');
            \Log::error('Session Data: ' . json_encode(Session::all()));
            
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc hết hạn, vui lòng đăng nhập lại',
                'redirect' => route('login')
            ], 401);
        }
        
        $file = $request->file('file');
        \Log::info('Gửi yêu cầu import file bệnh: ' . $file->getClientOriginalName());

        try {
            $apiUrl = '/api/diseases/import';
            \Log::info('Gửi yêu cầu đến: ' . $apiUrl);
            \Log::info('Token sử dụng: ' . substr($token, 0, 20) . '...');
            
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $token
            ])->attach(
                'file', 
                file_get_contents($file), 
                $file->getClientOriginalName()
            )->post($apiUrl);
            
            \Log::info('Status code: ' . $response->status());
            \Log::info('Phản hồi từ API: ' . $response->body());
            
            if ($response->status() === 302) {
                \Log::warning('Bị chuyển hướng 302 đến: ' . $response->header('Location'));
                return response()->json([
                    'success' => false,
                    'message' => 'Bị chuyển hướng, có thể do vấn đề xác thực',
                    'redirect' => $response->header('Location') ?: route('login')
                ], 401);
            }
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ API: ' . $response->status(),
                    'details' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi kết nối API: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Kiểm tra kết nối API
     */
    public function checkApiConnection()
    {
        try {
            $response = Http::get('http://localhost:3000/api/medicines');
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kết nối API thành công',
                    'status' => $response->status()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'API phản hồi với mã lỗi: ' . $response->status(),
                    'details' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kết nối đến API: ' . $e->getMessage()
            ], 500);
        }
    }
}
