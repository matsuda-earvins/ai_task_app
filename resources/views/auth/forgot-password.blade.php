@extends('layouts.app')

@section('title', 'パスワードリセット')

@section('content')
    <div class="auth-container">
        <div class="auth-content">
            <!-- ヘッダー -->
            <div class="auth-header">
                <a href="{{ route('login') }}" class="auth-back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="auth-header-title">パスワードリセット</h1>
                <div class="auth-header-spacer"></div>
            </div>

            <!-- 説明文 -->
            <div class="auth-description">
                <p>登録中のメールアドレスを入力してください</p>
            </div>

            <!-- フォーム -->
            <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                @csrf

                <!-- 成功メッセージ -->
                @if (session('status'))
                    <div class="auth-success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- エラーメッセージ -->
                @if ($errors->any())
                    <div class="auth-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- メールアドレス -->
                <div class="auth-field">
                    <label for="email" class="auth-label">
                        <i class="fas fa-envelope"></i>
                        <span>メールアドレス</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="auth-input"
                        placeholder="example@example.com" required autofocus>
                </div>

                <!-- 送信ボタン -->
                <button type="submit" class="auth-btn primary">
                    <i class="fas fa-paper-plane"></i>
                    <span>リセットリンクを送信</span>
                </button>

                <!-- ログインに戻る -->
                <div class="auth-footer">
                    <a href="{{ route('login') }}" class="auth-link-primary">
                        ログイン画面に戻る
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
