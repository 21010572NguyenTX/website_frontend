<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    /**
     * Lấy thông tin chi tiết của một thuốc
     */
    public function getMedicineById($id)
    {
        try {
            $response = Http::get('http://localhost:3000/api/medicines/' . $id);
            
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lấy thông tin chi tiết của một bệnh
     */
    public function getDiseaseById($id)
    {
        try {
            $response = Http::get('http://localhost:3000/api/diseases/' . $id);
            
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
}
