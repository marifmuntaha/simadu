<?php

namespace Database\Factories\Exam;

use App\Models\Exam\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'role_name' => 'Administrator',
            'role_desc' => 'Administrator'
        ];
    }
}
