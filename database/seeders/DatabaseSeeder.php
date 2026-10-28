<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Non-destructively seed sample readers and Arabic/English literary quotes if not already present.
     */
    public function run(): void
    {
        $arabicReader = User::firstOrCreate(
            ['email' => 'reader@quoteit.local'],
            [
                'name' => 'أديب القراءة',
                'password' => 'password123',
                'locale' => 'ar',
            ]
        );

        $englishReader = User::firstOrCreate(
            ['email' => 'editorial@quoteit.local'],
            [
                'name' => 'Clara Vance',
                'password' => 'password123',
                'locale' => 'en',
            ]
        );

        // Sample Quick User Quotes
        Quote::firstOrCreate(
            [
                'user_id' => $arabicReader->id,
                'type' => Quote::TYPE_QUICK,
                'quote_text' => 'القراءة تمنحنا حياة أخرى نعيشها فوق حياتنا، وتفتح لنا نوافذ لا تغلقها المسافات.',
            ]
        );

        Quote::firstOrCreate(
            [
                'user_id' => $englishReader->id,
                'type' => Quote::TYPE_QUICK,
                'quote_text' => 'Quiet libraries hold the loudest conversations across centuries of human thought.',
            ]
        );

        // Sample Book Quotes
        Quote::firstOrCreate(
            [
                'user_id' => $arabicReader->id,
                'type' => Quote::TYPE_BOOK,
                'quote_text' => 'إن الكلمات التي تخرج من القلب تدخل إلى القلب، وما يخرج من اللسان لا يتجاوز الآذان.',
            ],
            [
                'book_title' => 'وحي القلم',
                'attributed_author' => 'مصطفى صادق الرافعي',
                'book_cover_path' => null,
            ]
        );

        Quote::firstOrCreate(
            [
                'user_id' => $englishReader->id,
                'type' => Quote::TYPE_BOOK,
                'quote_text' => 'A room without books is like a body without a soul.',
            ],
            [
                'book_title' => 'Selected Letters',
                'attributed_author' => 'Marcus Tullius Cicero',
                'book_cover_path' => null,
            ]
        );
    }
}