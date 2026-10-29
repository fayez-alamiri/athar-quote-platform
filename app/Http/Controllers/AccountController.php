<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarRequest;
use App\Models\Quote;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the authenticated user's My Account page with real database statistics.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        $groupedQuotes = $user->quotes()
            ->select('type', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('type')
            ->pluck('aggregate', 'type');

        $quickQuotesCount = (int) ($groupedQuotes[Quote::TYPE_QUICK] ?? 0);
        $bookQuotesCount = (int) ($groupedQuotes[Quote::TYPE_BOOK] ?? 0);
        $totalQuotesCount = $quickQuotesCount + $bookQuotesCount;

        $receivedLikesCount = $user->receivedLikesCount();
        $savedQuotesCount = $user->savedQuotes()->count();

        return view('account.show', [
            'user' => $user,
            'stats' => [
                'total_quotes' => $totalQuotesCount,
                'quick_quotes' => $quickQuotesCount,
                'book_quotes' => $bookQuotesCount,
                'received_likes' => $receivedLikesCount,
                'saved_quotes' => $savedQuotesCount,
            ],
        ]);
    }

    /**
     * Upload and replace the authenticated user's profile avatar image.
     */
    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();
        $file = $request->file('avatar');

        $newPath = ImageStorage::store($file, 'avatars', 'avatar_' . $user->id);
        $oldPath = $user->avatar_path;

        $user->forceFill([
            'avatar_path' => $newPath,
        ])->save();

        ImageStorage::delete($oldPath, 'avatars');

        return redirect()->route('account.show')
            ->with('status', __('Your profile image has been updated.'));
    }

    /**
     * Remove the authenticated user's profile avatar and revert to the default avatar.
     */
    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $oldPath = $user->avatar_path;

        if (! empty($oldPath)) {
            ImageStorage::delete($oldPath, 'avatars');

            $user->forceFill([
                'avatar_path' => null,
            ])->save();
        }

        return redirect()->route('account.show')
            ->with('status', __('Your profile image has been removed.'));
    }
}