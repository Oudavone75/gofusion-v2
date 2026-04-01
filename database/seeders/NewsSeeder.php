<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = DB::table('news_categories')->pluck('id', 'name');

        $news_items = [
            [
                'title' => 'Exciting Company Expansion Announced',
                'description' => 'We are expanding to new regions to serve you better.',
                'category' => 'Company News',
                'company_id' => null,
            ],
            [
                'title' => 'Fusion App Updated to v2.0',
                'description' => 'Major performance and UI improvements.',
                'category' => 'Go Fusion Updates',
                'company_id' => null,
            ],
            [
                'title' => 'Hiring Now: Full Stack Developers',
                'description' => 'Join our amazing team of engineers.',
                'category' => 'Job',
                'company_id' => null,
            ],
            [
                'title' => 'Exclusive Job Fair Coming Soon',
                'description' => 'Don’t miss our upcoming virtual job fair.',
                'category' => 'Job',
                'company_id' => null,
            ],
        ];

        foreach ($news_items as $item) {
            DB::table('news')->insert([
                'title' => $item['title'],
                'description' => $item['description'],
                'category_id' => $categories[$item['category']],
                'company_id' => $item['company_id'],
                'status' => 'active',
                'published_at' => now(),
                'image_path' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
