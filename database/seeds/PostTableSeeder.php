<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Post;

class PostTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
		foreach (range(1, 100) as $i) {
			Post::create([
				'title'   => $faker->sentence(5),
				'content' => $faker->paragraph(4),
                'user_id' => App\Models\User::all()->random()->id
			]);
		}
    }
}
