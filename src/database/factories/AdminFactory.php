<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Admin;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition()
    {
        return [
            'name' => '管理者テスト',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ];
    }
}