<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     */
    public function showLoginForm()
    {
        // Nếu đã đăng nhập thì chuyển hướng về trang chủ
        if (Session::has('user')) {
            return redirect()->route('home');
        }
        
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Tên đăng nhập là bắt buộc',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            \Log::info('Đang gửi yêu cầu đăng nhập: ' . $request->email);
            
            $response = Http::post('http://localhost:3000/api/auth/login', [
                'username' => $request->email,
                'password' => $request->password,
            ]);

            $result = $response->json();
            \Log::info('Phản hồi từ API: ' . json_encode($result));

            if ($response->successful() && isset($result['success']) && $result['success']) {
                // Lưu thông tin người dùng vào session
                Session::put('user', $result['user']);
                Session::put('token', $result['token'] ?? null);
                
                \Log::info('Đăng nhập thành công cho: ' . $result['user']['username'] . ' - Quyền: ' . $result['user']['role']);
                \Log::info('Token (20 ký tự đầu): ' . substr($result['token'] ?? 'NO_TOKEN', 0, 20));
                
                return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
            } else {
                \Log::warning('Đăng nhập thất bại: ' . ($result['message'] ?? 'Không xác định'));
                return redirect()->back()->withErrors([
                    'credentials' => $result['message'] ?? 'Thông tin đăng nhập không chính xác'
                ])->withInput();
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi kết nối đến máy chủ: ' . $e->getMessage());
            return redirect()->back()->withErrors([
                'api_error' => 'Không thể kết nối đến máy chủ: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Hiển thị form đăng ký
     */
    public function showRegisterForm()
    {
        // Nếu đã đăng nhập thì chuyển hướng về trang chủ
        if (Session::has('user')) {
            return redirect()->route('home');
        }
        
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255',
            'password' => 'required|min:6|confirmed',
        ], [
            'email.required' => 'Tên đăng nhập là bắt buộc',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $response = Http::post('http://localhost:3000/api/auth/register', [
                'username' => $request->email,
                'password' => $request->password,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['success']) && $result['success']) {
                return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
            } else {
                return redirect()->back()->withErrors([
                    'register_error' => $result['message'] ?? 'Đăng ký thất bại. Vui lòng thử lại.'
                ])->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'api_error' => 'Không thể kết nối đến máy chủ: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Đăng xuất người dùng
     */
    public function logout()
    {
        Session::forget(['user', 'token']);
        return redirect()->route('login')->with('success', 'Đăng xuất thành công!');
    }

    /**
     * Hiển thị trang thông tin cá nhân
     */
    public function profile()
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem thông tin cá nhân');
        }
        
        $user = Session::get('user');
        return view('auth.profile', compact('user'));
    }

    /**
     * Hiển thị danh sách bệnh đã lưu
     */
    public function favoriteDiseases()
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem danh sách bệnh đã lưu');
        }

        $user = Session::get('user');
        $token = Session::get('token');

        try {
            $response = Http::withToken($token)
                ->get('http://localhost:3000/api/favorite/diseases', [
                    'user_id' => $user['id']
                ]);

            $result = $response->json();

            if ($response->successful() && isset($result['success']) && $result['success']) {
                $favoriteDiseases = $result['data'] ?? [];
                return view('favorite.diseases', compact('favoriteDiseases'));
            } else {
                return redirect()->back()->with('error', $result['message'] ?? 'Không thể lấy danh sách bệnh đã lưu');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Không thể kết nối đến máy chủ: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị danh sách thuốc đã lưu
     */
    public function favoriteMedicines()
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem danh sách thuốc đã lưu');
        }

        $user = Session::get('user');
        $token = Session::get('token');

        try {
            $response = Http::withToken($token)
                ->get('http://localhost:3000/api/favorite/medicines', [
                    'user_id' => $user['id']
                ]);

            $result = $response->json();

            if ($response->successful() && isset($result['success']) && $result['success']) {
                $favoriteMedicines = $result['data'] ?? [];
                return view('favorite.medicines', compact('favoriteMedicines'));
            } else {
                return redirect()->back()->with('error', $result['message'] ?? 'Không thể lấy danh sách thuốc đã lưu');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Không thể kết nối đến máy chủ: ' . $e->getMessage());
        }
    }

    /**
     * Thêm bệnh vào danh sách đã lưu
     */
    public function addFavoriteDisease(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện chức năng này'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'disease_id' => 'required|numeric',
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
            $response = Http::withToken($token)
                ->post('http://localhost:3000/api/favorite/diseases/add', [
                    'user_id' => $user['id'],
                    'disease_id' => $request->disease_id,
                    'note' => $request->note ?? ''
                ]);

            $result = $response->json();

            return response()->json([
                'success' => $response->successful() && isset($result['success']) && $result['success'],
                'message' => $result['message'] ?? 'Không thể thêm bệnh vào danh sách đã lưu',
                'data' => $result['data'] ?? null
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa bệnh khỏi danh sách đã lưu
     */
    public function removeFavoriteDisease(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện chức năng này'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'disease_id' => 'required|numeric',
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
            $response = Http::withToken($token)
                ->delete('http://localhost:3000/api/favorite/diseases/remove', [
                    'user_id' => $user['id'],
                    'disease_id' => $request->disease_id
                ]);

            $result = $response->json();

            return response()->json([
                'success' => $response->successful() && isset($result['success']) && $result['success'],
                'message' => $result['message'] ?? 'Không thể xóa bệnh khỏi danh sách đã lưu',
                'data' => $result['data'] ?? null
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thêm thuốc vào danh sách đã lưu
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
            $response = Http::withToken($token)
                ->post('http://localhost:3000/api/favorite/medicines/add', [
                    'user_id' => $user['id'],
                    'medicine_id' => $request->medicine_id,
                    'note' => $request->note ?? ''
                ]);

            $result = $response->json();

            return response()->json([
                'success' => $response->successful() && isset($result['success']) && $result['success'],
                'message' => $result['message'] ?? 'Không thể thêm thuốc vào danh sách đã lưu',
                'data' => $result['data'] ?? null
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa thuốc khỏi danh sách đã lưu
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

        $validator = Validator::make($request->all(), [
            'medicine_id' => 'required|numeric',
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
            $response = Http::withToken($token)
                ->delete('http://localhost:3000/api/favorite/medicines/remove', [
                    'user_id' => $user['id'],
                    'medicine_id' => $request->medicine_id
                ]);

            $result = $response->json();

            return response()->json([
                'success' => $response->successful() && isset($result['success']) && $result['success'],
                'message' => $result['message'] ?? 'Không thể xóa thuốc khỏi danh sách đã lưu',
                'data' => $result['data'] ?? null
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function updateProfile(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để cập nhật thông tin cá nhân');
        }

        // Log dữ liệu đầu vào để debug - đảm bảo tham số context là mảng
        \Log::info('Dữ liệu cập nhật profile:', $request->all() ?? []);

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.max' => 'Họ tên không được vượt quá 255 ký tự',
            'email.max' => 'Email không được vượt quá 255 ký tự',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự',
            'avatar.image' => 'Tệp phải là hình ảnh',
            'avatar.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif',
            'avatar.max' => 'Kích thước hình ảnh không được vượt quá 2MB',
        ]);

        if ($validator->fails()) {
            \Log::error('Lỗi validation:', $validator->errors()->toArray() ?? []);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Session::get('user');
        $token = Session::get('token');
        $userData = [
            'id' => $user['id'],
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        // Xử lý upload avatar nếu có
        if ($request->hasFile('avatar')) {
            try {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $userData['avatar'] = $avatarPath;
                \Log::info('Avatar đã được upload:', ['path' => $avatarPath]);
            } catch (\Exception $e) {
                \Log::error('Lỗi khi upload avatar:', ['error' => $e->getMessage()]);
            }
        }

        try {
            \Log::info('Gửi dữ liệu cập nhật đến API:', $userData ?? []);
            
            $response = Http::withToken($token)
                ->post('http://localhost:3000/api/users/update', $userData);

            $result = $response->json();
            \Log::info('Kết quả API trả về:', $result ?? []);

            if ($response->successful() && isset($result['success']) && $result['success']) {
                // Cập nhật thông tin người dùng trong session
                $updatedUser = $result['data'] ?? $userData;
                $newUserData = array_merge($user, $updatedUser);
                Session::put('user', $newUserData);
                
                \Log::info('Session đã được cập nhật:', ['user' => $newUserData ?? []]);
                
                return redirect()->route('profile')->with('success', 'Cập nhật thông tin thành công!');
            } else {
                \Log::error('API trả về lỗi:', $result ?? []);
                return redirect()->back()->withErrors([
                    'update_error' => $result['message'] ?? 'Cập nhật thông tin thất bại. Vui lòng thử lại.'
                ])->withInput();
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi kết nối API:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors([
                'api_error' => 'Không thể kết nối đến máy chủ: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Đổi mật khẩu người dùng
     */
    public function changePassword(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đổi mật khẩu');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Mật khẩu hiện tại là bắt buộc',
            'current_password.min' => 'Mật khẩu hiện tại phải có ít nhất 6 ký tự',
            'new_password.required' => 'Mật khẩu mới là bắt buộc',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Session::get('user');
        $token = Session::get('token');

        try {
            $response = Http::withToken($token)
                ->post('http://localhost:3000/api/auth/change-password', [
                    'id' => $user['id'],
                    'current_password' => $request->current_password,
                    'new_password' => $request->new_password,
                ]);

            $result = $response->json();

            if ($response->successful() && isset($result['success']) && $result['success']) {
                return redirect()->route('profile')->with('success', 'Đổi mật khẩu thành công!');
            } else {
                return redirect()->back()->withErrors([
                    'password_error' => $result['message'] ?? 'Đổi mật khẩu thất bại. Vui lòng thử lại.'
                ]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'api_error' => 'Không thể kết nối đến máy chủ: ' . $e->getMessage()
            ]);
        }
    }
}