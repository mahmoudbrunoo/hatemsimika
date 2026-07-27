<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstNames = ['محمد', 'أحمد', 'محمود', 'مصطفى', 'عمر', 'خالد', 'يوسف', 'كريم', 'حسن', 'علي'];

        // اسم رباعي عربي يطابق قاعدة ArabicQuadName
        $quadName = implode(' ', fake()->randomElements($firstNames, 4));

        return [
            'name' => $quadName,
            'email' => fake()->unique()->safeEmail(),
            'phone' => '010' . fake()->unique()->numerify('########'),
            'father_phone' => '011' . fake()->numerify('########'),
            'mother_phone' => '012' . fake()->numerify('########'),
            'national_id' => '3' . fake()->unique()->numerify('#############'),
            'governorate' => fake()->randomElement(User::GOVERNORATES),
            'school' => 'مدرسة ' . fake()->randomElement(['النصر', 'المستقبل', 'الأورمان', 'طه حسين']) . ' الثانوية',
            'academic_year' => fake()->numberBetween(1, 3),
            'gender' => fake()->randomElement(['male', 'female']),
            'status' => User::STATUS_APPROVED,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** حساب لسه قيد المراجعة */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => User::STATUS_PENDING,
        ]);
    }
}
