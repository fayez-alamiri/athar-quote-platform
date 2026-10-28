<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookQuoteRequest;
use App\Http\Requests\StoreQuickQuoteRequest;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuoteController extends Controller
{
    /**
     * Display the public quote feed with type filtering, search, sorting, and pagination.
     */
    public function index(Request $request): View
    {
        $type = $request->query('type');
        if (! in_array($type, [Quote::TYPE_QUICK, Quote::TYPE_BOOK], true)) {
            $type = 'all';
        }

        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'latest');
        if (! in_array($sort, ['latest', 'popular'], true)) {
            $sort = 'latest';
        }

        $query = Quote::query()
            ->with(['user:id,name,avatar_path'])
            ->withCount('likes')
            ->filterByType($type === 'all' ? null : $type)
            ->search($search);

        if (Auth::check()) {
            $userId = Auth::id();
            $query->withExists([
                'likes as is_liked_by_auth' => fn ($q) => $q->where('user_id', $userId),
                'saves as is_saved_by_auth' => fn ($q) => $q->where('user_id', $userId),
            ]);
        }

        if ($sort === 'popular') {
            $query->orderByDesc('likes_count')->latest('id');
        } else {
            $query->latest('created_at')->latest('id');
        }

        $quotes = $query->paginate(9)->withQueryString();

        // Aggregate counts for filter tabs (scoped to current search term if active)
        $baseCountQuery = Quote::query()->search($search);
        $counts = [
            'all' => (clone $baseCountQuery)->count(),
            'quick' => (clone $baseCountQuery)->quick()->count(),
            'book' => (clone $baseCountQuery)->book()->count(),
        ];

        return view('quotes.index', [
            'quotes' => $quotes,
            'activeType' => $type,
            'search' => $search,
            'sort' => $sort,
            'counts' => $counts,
        ]);
    }

    /**
     * Show the form for publishing a quick user quote.
     */
    public function createQuick(): View
    {
        return view('quotes.create-quick');
    }

    /**
     * Validate and store a newly published quick user quote.
     */
    public function storeQuick(StoreQuickQuoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Quote::create([
            'user_id' => $request->user()->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => $validated['quote_text'],
            'book_title' => null,
            'book_cover_path' => null,
            'attributed_author' => null,
        ]);

        return redirect()->route('home')
            ->with('status', __('Your quick quote has been published successfully.'));
    }

    /**
     * Show the form for publishing a quote from a book.
     */
    public function createBook(): View
    {
        return view('quotes.create-book');
    }

    /**
     * Validate and store a newly published book quote with its uploaded cover image.
     */
    public function storeBook(StoreBookQuoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $file = $request->file('book_cover');

        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $safeFilename = 'cover_' . Str::uuid()->toString() . '.' . $extension;
        $storedPath = $file->storeAs('book-covers', $safeFilename, 'public');

        Quote::create([
            'user_id' => $request->user()->id,
            'type' => Quote::TYPE_BOOK,
            'quote_text' => $validated['quote_text'],
            'book_title' => $validated['book_title'],
            'book_cover_path' => $storedPath,
            'attributed_author' => $validated['attributed_author'],
        ]);

        return redirect()->route('home')
            ->with('status', __('Your book quote has been published successfully.'));
    }
}