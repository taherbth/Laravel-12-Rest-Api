<?php 
namespace Database\Factories;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'customer_no' => $this->faker->numerify('#####'),
            'first_name'  => $this->faker->firstName(),
            'last_name'   => $this->faker->lastName(),
            'gender_id'   => 1,
            'address'     => $this->faker->address(),
            'city'        => 'Dhaka',
            'country_id'  => 23,
        ];
    }
}
?>