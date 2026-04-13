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
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');

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
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@test.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors();

        $this->assertEquals(
            'ログイン情報が登録されていません',
            session('errors')->first()
        );
    }
}