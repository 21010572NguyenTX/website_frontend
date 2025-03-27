@extends('layouts.app')

@section('title', 'Lỗi kết nối')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">Lỗi kết nối API</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <p class="mb-0">{{ $message ?? 'Không thể kết nối với API. Vui lòng thử lại sau.' }}</p>
                </div>
                <div class="mt-4">
                    <p>Có thể do một trong các nguyên nhân sau:</p>
                    <ul>
                        <li>API backend không hoạt động</li>
                        <li>Đường dẫn API không chính xác</li>
                        <li>Vấn đề về kết nối mạng</li>
                    </ul>
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('home') }}" class="btn btn-primary">Quay lại trang chủ</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection