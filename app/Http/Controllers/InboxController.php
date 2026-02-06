<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InboxController extends Controller
{
    /**
     * GET /api/inbox — List conversations for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::forUser($user->id)
            ->with(['userOne:id,name,username', 'userTwo:id,name,username', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        $mapped = $conversations->through(function (Conversation $conv) use ($user) {
            $otherId = $conv->otherUserId($user->id);
            $other = $conv->user_one_id === $otherId ? $conv->userOne : $conv->userTwo;

            $unreadCount = Message::where('conversation_id', $conv->id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();

            return [
                'id' => $conv->id,
                'other_user' => [
                    'id' => $other->id,
                    'name' => $other->name,
                    'username' => $other->username,
                ],
                'last_message' => $conv->latestMessage ? [
                    'body' => $conv->latestMessage->body,
                    'sender_id' => $conv->latestMessage->sender_id,
                    'created_at' => $conv->latestMessage->created_at->toISOString(),
                ] : null,
                'unread_count' => $unreadCount,
                'last_message_at' => $conv->last_message_at?->toISOString(),
            ];
        });

        return response()->json($mapped);
    }

    /**
     * GET /api/inbox/{conversation} — Get messages in a conversation.
     */
    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if (! $conversation->hasParticipant($user->id)) {
            return response()->json(['message' => 'Bu konuşmaya erişiminiz yok.'], Response::HTTP_FORBIDDEN);
        }

        // Mark unread messages as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()
            ->with('sender:id,name,username')
            ->paginate(50);

        $otherId = $conversation->otherUserId($user->id);
        $other = User::select('id', 'name', 'username')->find($otherId);

        return response()->json([
            'conversation_id' => $conversation->id,
            'other_user' => $other,
            'messages' => $messages,
        ]);
    }

    /**
     * POST /api/inbox/{user}/send — Send a message to a user.
     */
    public function send(Request $request, User $user)
    {
        $sender = $request->user();

        if ($sender->id === $user->id) {
            return response()->json(['message' => 'Kendinize mesaj gönderemezsiniz.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = Conversation::findOrCreateBetween($sender->id, $user->id);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => $data['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'message' => [
                'id' => $message->id,
                'conversation_id' => $conversation->id,
                'sender_id' => $message->sender_id,
                'body' => $message->body,
                'created_at' => $message->created_at->toISOString(),
            ],
        ], Response::HTTP_CREATED);
    }
}
