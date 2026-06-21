<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function pairIds(int $userA, int $userB): array
    {
        return [min($userA, $userB), max($userA, $userB)];
    }

    public function findConversation(int $userId, int $otherId): ?Conversation
    {
        [$userOne, $userTwo] = $this->pairIds($userId, $otherId);

        return Conversation::where('user_one', $userOne)
            ->where('user_two', $userTwo)
            ->first();
    }

    public function findOrCreateConversation(int $userId, int $otherId): Conversation
    {
        [$userOne, $userTwo] = $this->pairIds($userId, $otherId);

        return Conversation::firstOrCreate([
            'user_one' => $userOne,
            'user_two' => $userTwo,
        ]);
    }

    public function otherUser(Conversation $conversation, int $userId): User
    {
        $otherId = $conversation->user_one === $userId
            ? $conversation->user_two
            : $conversation->user_one;

        return User::findOrFail($otherId);
    }

    public function unreadCount(int $userId): int
    {
        return Message::where('is_read', false)
            ->where('sender_id', '!=', $userId)
            ->whereHas('conversation', fn ($q) => $q
                ->where('user_one', $userId)
                ->orWhere('user_two', $userId))
            ->count();
    }

    public function inboxForUser(int $userId): Collection
    {
        return collect(DB::select("
            SELECT c.id AS conv_id, c.updated_at,
                   CASE WHEN c.user_one = ? THEN c.user_two ELSE c.user_one END AS other_id,
                   u.name AS other_name, u.role AS other_role,
                   (SELECT m.body FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) AS last_message,
                   (SELECT m.created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) AS last_message_at,
                   (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.sender_id != ? AND m.is_read = 0) AS unread_count
            FROM conversations c
            JOIN users u ON u.id = CASE WHEN c.user_one = ? THEN c.user_two ELSE c.user_one END
            WHERE c.user_one = ? OR c.user_two = ?
            ORDER BY c.updated_at DESC
        ", [$userId, $userId, $userId, $userId, $userId]));
    }

    public function markIncomingAsRead(Conversation $conversation, int $readerId, int $senderId): void
    {
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $senderId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
