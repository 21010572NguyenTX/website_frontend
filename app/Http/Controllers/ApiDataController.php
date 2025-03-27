<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiDataController extends Controller
{
    public function medicines(Request $request)
    {
        try {
            // Lấy thông tin trang từ request, mặc định là trang 1
            $page = $request->query('page', 1);
            // Số lượng thuốc mỗi trang, mặc định là 12 (phù hợp với hiển thị dạng card)
            $limit = $request->query('limit', 12);
            
            // Sử dụng API phân trang thay vì lấy tất cả dữ liệu
            $response = Http::get('http://localhost:3000/api/medicines/paginated', [
                'page' => $page,
                'limit' => $limit
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                
                // Kiểm tra và đảm bảo mảng dữ liệu
                if (!isset($result['data']) || !is_array($result['data'])) {
                    $result['data'] = [];
                }
                
                return view('medicines', [
                    'medicines' => $result['data'],
                    'pagination' => [
                        'currentPage' => $result['currentPage'] ?? 1,
                        'totalPages' => $result['totalPages'] ?? 1,
                        'hasNextPage' => $result['hasNextPage'] ?? false,
                        'hasPreviousPage' => $result['hasPreviousPage'] ?? false,
                        'totalItems' => $result['totalMedicines'] ?? 0
                    ]
                ]);
            } else {
                return view('error', ['message' => 'Không thể kết nối với API thuốc']);
            }
        } catch (\Exception $e) {
            return view('error', ['message' => 'Lỗi kết nối API: ' . $e->getMessage()]);
        }
    }

    public function medicineDetail($id)
    {
        try {
            $response = Http::get('http://localhost:3000/api/medicines/' . $id);
            
            if ($response->successful()) {
                $result = $response->json();
                
                // Kiểm tra dữ liệu trả về
                if (!isset($result['data']) || !is_array($result['data'])) {
                    return view('error', ['message' => 'Dữ liệu thuốc không đúng định dạng']);
                }
                return view('medicine-detail', ['medicine' => $result['data']]);
            } else {
                return view('error', ['message' => 'Không thể tìm thấy thông tin thuốc']);
            }
        } catch (\Exception $e) {
            return view('error', ['message' => 'Lỗi kết nối API: ' . $e->getMessage()]);
        }
    }

    public function diseases(Request $request)
    {
        try {
            // Lấy thông tin trang từ request, mặc định là trang 1
            $page = $request->query('page', 1);
            // Số lượng bệnh mỗi trang, mặc định là 12 (phù hợp với hiển thị dạng card)
            $limit = $request->query('limit', 12);
            
            // Sử dụng API phân trang thay vì lấy tất cả dữ liệu
            $response = Http::get('http://localhost:3000/api/diseases/paginated', [
                'page' => $page,
                'limit' => $limit
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                
                // Kiểm tra và đảm bảo mảng dữ liệu
                if (!isset($result['data']) || !is_array($result['data'])) {
                    $result['data'] = [];
                }
                
                return view('diseases', [
                    'diseases' => $result['data'],
                    'pagination' => [
                        'currentPage' => $result['currentPage'] ?? 1,
                        'totalPages' => $result['totalPages'] ?? 1,
                        'hasNextPage' => $result['hasNextPage'] ?? false,
                        'hasPreviousPage' => $result['hasPreviousPage'] ?? false,
                        'totalItems' => $result['totalDiseases'] ?? 0
                    ]
                ]);
            } else {
                return view('error', ['message' => 'Không thể kết nối với API bệnh']);
            }
        } catch (\Exception $e) {
            return view('error', ['message' => 'Lỗi kết nối API: ' . $e->getMessage()]);
        }
    }

    public function diseaseDetail($id)
    {
        try {
            $response = Http::get('http://localhost:3000/api/diseases/' . $id);
            
            if ($response->successful()) {
                $result = $response->json();
                
                // Kiểm tra dữ liệu trả về
                if (!isset($result['data']) || !is_array($result['data'])) {
                    return view('error', ['message' => 'Dữ liệu bệnh không đúng định dạng']);
                }
                return view('disease-detail', ['disease' => $result['data']]);
            } else {
                return view('error', ['message' => 'Không thể tìm thấy thông tin bệnh']);
            }
        } catch (\Exception $e) {
            return view('error', ['message' => 'Lỗi kết nối API: ' . $e->getMessage()]);
        }
    }

    public function home()
    {
        return view('home');
    }
}