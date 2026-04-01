<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventOdds;
use App\Models\Theme;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function __construct(private Event $event) {}
    public function storeEventByCrawler($request = [])
    {
        try {
            DB::beginTransaction();
            $start_date = $request['start_date'];
            $end_date = $request['end_date'];
            $event_data = [
                'title' => $request['title'],
                'description' => $request['description'],
                'short_description' => $request['short_description'],
                'image' => $request['imageUrl'],
                'event_type' => 'onsite',
                'location' => $request['address'],
                'start_date' => $start_date,
                'end_date' => $end_date,
                'qr_code' => \Str::uuid(),
                'status' => 'active'
            ];
            $event_model = $this->event::updateOrCreate(
                ['title' => $request['title'], 'start_date' => $start_date, 'end_date' => $end_date],
                $event_data
            );
            if (isset($request['categories'])) {
                $category_ids = [];
                foreach ($request['categories'] as $category) {
                    $slug = generateSlug($category);
                    $category_model = Category::firstOrCreate([
                        'name' => $category
                    ], [
                        'name' => $category,
                        'slug' => $slug
                    ]);
                    $category_ids[] = $category_model->id;
                }
                if (!empty($category_ids)) {
                    $event_model->eventCategories()->sync($category_ids);
                }
            }
            if (isset($request['ODDS']) && is_array($request['ODDS'])) {
                $cleaned_odds = preg_replace('/^ODD\s\d+\.\s*/', '', $request['ODDS']);
                $theme_ids = Theme::where('french_name', $cleaned_odds)->pluck('id')->toArray();
                if (count($theme_ids) > 0) {
                    $event_model->themes()->sync($theme_ids);
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => $th->getMessage()
            ];
        }
        return [
            'status' => true,
            'message' => 'Event crawl successfully!',
            'data' => $event_model
        ];
    }
}
