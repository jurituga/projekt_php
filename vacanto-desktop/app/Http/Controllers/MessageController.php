<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messages,
        private NotificationService $notifications,
    ) {}

    public function inbox(): View
    {
        $conversations = $this->messages->inboxForUser(auth()->id());

        return view('messages.inbox', compact('conversations'));
    }

    public function chat(User $user): View|RedirectResponse
    {
        if ($user->id === auth()->id() || $user->role === UserRole::Admin) {
            return redirect()->route('messages.inbox');
        }

        $conversation = $this->messages->findConversation(auth()->id(), $user->id);
        $messages = collect();

        if ($conversation) {
            $this->messages->markIncomingAsRead($conversation, auth()->id(), $user->id);
            $messages = $conversation->messages()->orderBy('created_at')->get();
        }

        return view('messages.chat', [
            'otherUser' => $user,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id() || $user->role === UserRole::Admin) {
            return redirect()->route('messages.inbox');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = $this->messages->findOrCreateConversation(auth()->id(), $user->id);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->touch();

        $this->notifications->send(
            $user,
            'New message',
            auth()->user()->name.' sent you a message.',
            route('messages.chat', auth()->user()),
            'message'
        );

        return redirect()->route('messages.chat', $user);
    }

    public function poll(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['messages' => [], 'unread_total' => 0]);
        }

        $userId = auth()->id();
        $otherId = (int) $request->query('with', 0);
        $afterId = (int) $request->query('after', 0);
        $newMessages = [];

        if ($otherId && $afterId) {
            $conversation = $this->messages->findConversation($userId, $otherId);

            if ($conversation) {
                $this->messages->markIncomingAsRead($conversation, $userId, $otherId);

                $rows = Message::where('conversation_id', $conversation->id)
                    ->where('id', '>', $afterId)
                    ->orderBy('created_at')
                    ->get();

                foreach ($rows as $row) {
                    $newMessages[] = [
                        'id' => $row->id,
                        'is_mine' => $row->sender_id === $userId,
                        'body' => $row->body,
                        'time' => $row->created_at->format('g:i A'),
                    ];
                }
            }
        }

        return response()->json([
            'messages' => $newMessages,
            'unread_total' => $this->messages->unreadCount($userId),
        ]);
    }
}
