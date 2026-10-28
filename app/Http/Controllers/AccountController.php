<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the authenticated user's My Account page with real database statistics.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        $totalQuotesCount = $user->quotes()->count();
        $quickQuotesCount = $user->quotes()->quick()->count();
        $bookQuotesCount = $user->quotes()->book()->count();
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

        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $safeFilename = 'avatar_' . $user->id . '_' . Str::uuid()->toString() . '.' . $extension;
        $newPath = $file->storeAs('avatars', $safeFilename, 'public');

        $oldPath = $user->avatar_path;

        $user->forceFill([
            'avatar_path' => $newPath,
        ])->save();

        if (! empty($oldPath) && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

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
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $user->forceFill([
                'avatar_path' => null,
            ])->save();
        }

        return redirect()->route('account.show')
            ->with('status', __('Your profile image has been removed.'));
    }
}