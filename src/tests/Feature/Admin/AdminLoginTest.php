<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレス未入力でバリデーションエラーになる()
    {
        // ユーザー作成
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // メール未入力でログイン
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        // バリデーション確認
        $response->assertSessionHasErrors('email');

        // メッセージ確認
        $this->assertEquals(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

    /** @test */
    public function パスワード未入力でバリデーションエラーになる()
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertEquals(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    /** @test */
    public function ログイン情報が間違っているとエラーになる()
    {
        // 正しいユーザー作成
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // 間違った情報でログイン
        $response = $this->post('/admin/login', [
            'email' => 'wrong@test.com',
            'password' => 'password',
        ]);

        // エラーメッセージ確認
        $response->assertSessionHasErrors();

        $this->assertEquals(
            'ログイン情報が登録されていません',
            session('errors')->first()
        );
    }
}