<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Laravelの認証機能を利用するFacade
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Password;

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

        // チェックボックスの値を取得（ON なら true)
        $remember = $request->boolean('remember');

        // ログイン試行
        // Auth::attempt() : データベースのユーザー情報とメールアドレスとパスワード（ハッシュ化）が一致していたらtrueを返す
        // 第二引数に true / falseを指定することで、ログイン情報を保持するかどうかを指定できる
        if (Auth::attempt($credentials, $remember)) {
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


    // ログアウト処理を行うメソッド
    public function logout(Request $request)
    {
        // ログアウト
        Auth::logout();

        // ログイン情報等のセッションデータを無効化
        $request->session()->invalidate();

        // CSRF トークンを再生成
        $request->session()->regenerateToken();

        // ログイン画面にリダイレクト
        return redirect()->route('login');
    }


    // 新規登録画面を表示するメソッド
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // 新規登録処理を行うメソッド
    public function register(Request $request)
    {
        // バリデーションチェック（入力チェック）
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:ai_tasks_M_users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // ユーザーを作成
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'active_flg' => true,
        ]);

        // 作成したユーザーで自動ログイン
        Auth::login($user);

        // トップページにリダイレクト
        return redirect()->route('tasks.index');
    }


    // パスワードリセット申請画面表示
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }
    // パスワードリセットメール送信
    public function sendResetLinkEmail(Request $request)
    {
        // メールアドレスのバリデーション
        $request->validate(['email' => 'required|email']);
        // パスワードリセットリンクを送信
        $status = Password::sendResetLink(
            $request->only('email')
        );
        // 送信結果に応じてメッセージを表示
        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => 'パスワードリセット用のリンクをメールで送信しました。'])
            : back()->withErrors(['email' => 'このメールアドレスは登録されていません。']);
    }
    // パスワードリセット画面表示
    public function showResetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }
    // パスワードリセット処理
    public function resetPassword(Request $request)
    {
        // バリデーション
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        // パスワードをリセット
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password
                ])->save();
            }
        );
        // リセット結果に応じてリダイレクト
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'パスワードを再設定しました。ログインしてください。')
            : back()->withErrors(['email' => 'パスワードのリセットに失敗しました。']);
    }
}
