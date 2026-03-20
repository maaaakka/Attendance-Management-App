<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合バリデーションエラーになる()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password'
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function パスワードが未入力の場合バリデーションエラーになる()
    {
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => ''
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function ログイン情報が一致しない場合エラーになる()
    {
        User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@test.com',
            'password' => 'password'
        ]);

        $response->assertSessionHasErrors();
    }

}