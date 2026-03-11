<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Laravelの認証機能を利用するFacade
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ログイン画面を表示するメソッド
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // ログイン処理を行うメソッド
    public function login(Request $request)
    {
        // バリデーションチェック（入力チェック）
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // ログイン試行
        // Auth::attempt() : データベースのユーザー情報とメールアドレスとパスワード（ハッシュ化）が一致していたらtrueを返す
        if (Auth::attempt($credentials)) {
            // ログイン成功
            // session()->regenerate() : セッションIDを再生成する（セキュリティ対策）
            $request->session()->regenerate();
            // intended() : 元々アクセスしようとしていたページに移動（なければ /(トップページ) に移動）
            return redirect()->intended('/');
        }

        // ログイン失敗
        // back() : 前のページにリダイレクト
        // withErrors() : エラーメッセージをセッションに保存
        // onlyInput('email') : メールアドレスだけ入力を保持
        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ])->onlyInput('email');
    }
}
