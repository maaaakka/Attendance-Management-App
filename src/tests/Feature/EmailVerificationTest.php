<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
public function 会員登録後認証メールが送信される()
{
    Notification::fake();

    $this->post('/register',[
        'name'=>'テストユーザー',
        'email'=>'test@example.com',
        'password'=>'password',
        'password_confirmation'=>'password'
    ]);

    $user = \App\Models\User::first();

    Notification::assertSentTo(
        $user,
        VerifyEmail::class
    );
}

    /** @test */
public function 認証はこちらからボタンでメール認証画面へ遷移する()
{
    $user = \App\Models\User::factory()->create([
        'email_verified_at'=>null
    ]);

    $response = $this->actingAs($user)
        ->get('/email/verify');

    $response->assertStatus(200);
}

//** @test */
public function メール認証完了後勤怠画面へ遷移する()
{
    $user = \App\Models\User::factory()->create([
        'email_verified_at' => null
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->email)
        ]
    );

    $response = $this->actingAs($user)
        ->get($verificationUrl);

    $response->assertRedirect('/redirect?verified=1');
}
}