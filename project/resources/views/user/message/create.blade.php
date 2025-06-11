@extends('layouts.front')

@section('content')
<div class="chat-container">
    <div class="container">
        <div class="chat-layout">
            <!-- Sidebar -->
            @include('includes.user.sidebar')
            
            <!-- Main Chat Area -->
            <div class="chat-main">
                <!-- Chat Header -->
                <div class="chat-header">
                    <a href="{{ url()->previous() }}" class="back-btn">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <div class="chat-user-info">
                        @if ($user->id == $conv->sent->id)
                            @php $chatPartner = $conv->recieved; @endphp
                        @else
                            @php $chatPartner = $conv->sent; @endphp
                        @endif
                        
                        <div class="chat-user-avatar">
                            <img src="{{ $chatPartner->photo != null ? ($chatPartner->is_provider == 1 ? $chatPartner->photo : asset('assets/images/users/' . $chatPartner->photo)) : asset('assets/images/noimage.png') }}" 
                                alt="{{ $chatPartner->name }}" class="avatar-img">
                        </div>
                        <div class="chat-user-details">
                            <h3 class="chat-user-name">{{ $chatPartner->name }}</h3>
                            <span class="chat-user-status">Online</span>
                        </div>
                    </div>
                </div>
                
                <!-- Messages Container -->
                <div class="messages-container" id="messagesContainer">
                    @foreach ($conv->messages as $message)
                        @php 
                            // Check if the message is from the current logged-in user
                            $isFromCurrentUser = ($user->id == $message->conversation->sent->id && $message->sent_user != null) || 
                                               ($user->id == $message->conversation->recieved->id && $message->sent_user == null);
                            
                            // Determine sender for avatar and name display
                            $sender = $message->sent_user != null ? $message->conversation->sent : $message->conversation->recieved;
                            $senderPhoto = $sender->photo != null ? 
                                ($sender->is_provider == 1 ? $sender->photo : asset('assets/images/users/' . $sender->photo)) 
                                : asset('assets/images/noimage.png');
                            $timeAgo = \Carbon\Carbon::parse($message->created_at)->diffForHumans();
                        @endphp
                        
                        <div class="message-wrapper {{ $isFromCurrentUser ? 'message-outgoing' : 'message-incoming' }}">
                            @if (!$isFromCurrentUser)
                            <div class="message-avatar">
                                <img src="{{ $senderPhoto }}" alt="{{ $sender->name }}" class="avatar-img">
                            </div>
                            @endif
                            <div class="message-content">
                                @if (!$isFromCurrentUser)
                                <div class="message-sender-name">{{ $sender->name }}</div>
                                @endif
                                <div class="message-bubble">
                                    <p>{{ $message->message }}</p>
                                </div>
                                <div class="message-time">
                                    <span>{{ $timeAgo }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Message Input Form -->
                <div class="message-form-container">
                    <form action="{{ route('user-message-post') }}" method="POST" class="message-form">
                        @csrf
                        <input type="hidden" name="conversation_id" value="{{ $conv->id }}">
                        @if ($user->id == $conv->sent_user)
                            <input type="hidden" name="sent_user" value="{{ $conv->sent->id }}">
                            <input type="hidden" name="reciever" value="{{ $conv->recieved->id }}">
                        @else
                            <input type="hidden" name="reciever" value="{{ $conv->sent->id }}">
                            <input type="hidden" name="recieved_user" value="{{ $conv->recieved->id }}">
                        @endif
                        
                        <div class="message-input-wrapper">
                            <textarea name="message" id="message-input" placeholder="Type your message..." rows="1"></textarea>
                            <div class="message-actions">
                                <button type="button" class="action-btn emoji-btn">
                                    <i class="far fa-smile"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="send-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Chat Page Styles */
:root {
    --primary-color: #4F46E5;
    --primary-light: #EEF2FF;
    --secondary-color: #6B7280;
    --text-dark: #1F2937;
    --text-light: #6B7280;
    --background-light: #F9FAFB;
    --border-color: #E5E7EB;
    --message-sent: #25D366;  /* Green color for sent messages (WhatsApp style) */
    --message-received: #FFFFFF; /* White background for received messages */
    --border-radius: 12px;
    --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.chat-container {
    padding: 20px 0;
    background-color: var(--background-light);
    min-height: calc(100vh - 100px);
}

.chat-layout {
    display: flex;
    background-color: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    height: calc(100vh - 140px);
    min-height: 600px;
}

/* Adjust sidebar if needed */
.chat-layout > div:first-child {
    width: 280px;
    border-right: 1px solid var(--border-color);
    overflow-y: auto;
}

.chat-main {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
}

/* Chat Header */
.chat-header {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    background-color: #fff;
}

.back-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: var(--text-dark);
    background-color: var(--background-light);
    margin-right: 15px;
    transition: all 0.2s ease;
}

.back-btn:hover {
    background-color: var(--border-color);
}

.chat-user-info {
    display: flex;
    align-items: center;
}

.chat-user-avatar {
    margin-right: 12px;
}

.avatar-img {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary-light);
}

.chat-user-details {
    display: flex;
    flex-direction: column;
}

.chat-user-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
    margin: 0;
}

.chat-user-status {
    font-size: 13px;
    color: #10B981;
}

/* Messages Container */
.messages-container {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background-color: var(--background-light);
}

.message-wrapper {
    display: flex;
    max-width: 75%;
}

.message-incoming {
    align-self: flex-start;
}

.message-outgoing {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    margin-right: 10px;
    align-self: flex-end;
}

.message-outgoing .message-avatar {
    margin-right: 0;
    margin-left: 10px;
}

.message-avatar .avatar-img {
    width: 36px;
    height: 36px;
}

.message-content {
    display: flex;
    flex-direction: column;
}

.message-sender-name {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-light);
    margin-bottom: 4px;
    padding-left: 12px;
}

.message-bubble {
    background-color: var(--message-received);
    border-radius: 18px 18px 18px 4px;
    padding: 12px 16px;
    position: relative;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    color: var(--text-dark);
}

.message-outgoing .message-bubble {
    background-color: var(--message-sent);
    color: white;
    border-radius: 18px 18px 4px 18px;
}

.message-bubble p {
    margin: 0;
    font-size: 15px;
    line-height: 1.5;
}

.message-time {
    font-size: 12px;
    color: var(--text-light);
    margin-top: 4px;
    align-self: flex-start;
}

.message-outgoing .message-time {
    align-self: flex-end;
}

/* Message Form */
.message-form-container {
    padding: 16px 20px;
    border-top: 1px solid var(--border-color);
    background-color: #fff;
}

.message-form {
    display: flex;
    align-items: center;
    gap: 12px;
}

.message-input-wrapper {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
    background-color: var(--background-light);
    border-radius: 24px;
    border: 1px solid var(--border-color);
    transition: all 0.2s ease;
}

.message-input-wrapper:focus-within {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

#message-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 12px 16px;
    resize: none;
    max-height: 120px;
    font-size: 15px;
    color: var(--text-dark);
    border-radius: 24px;
    outline: none;
}

.message-actions {
    display: flex;
    padding-right: 12px;
}

.action-btn {
    background: none;
    border: none;
    color: var(--secondary-color);
    cursor: pointer;
    font-size: 18px;
    padding: 6px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.action-btn:hover {
    color: var(--primary-color);
    background-color: var(--primary-light);
}

.send-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--primary-color);
    color: white;
    border: none;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
}

.send-btn:hover {
    background-color: #4338CA;
    transform: translateY(-2px);
}

/* Responsive Styles */
@media (max-width: 992px) {
    .chat-layout > div:first-child {
        width: 240px;
    }
}

@media (max-width: 768px) {
    .chat-layout {
        flex-direction: column;
    }
    
    .chat-layout > div:first-child {
        width: 100%;
        height: auto;
        max-height: 0;
        overflow: hidden;
        border-right: none;
        border-bottom: 1px solid var(--border-color);
        transition: max-height 0.3s ease;
    }
    
    .chat-layout > div:first-child.active {
        max-height: 300px;
    }
    
    .chat-main {
        height: 100%;
    }
    
    .message-wrapper {
        max-width: 85%;
    }
}

@media (max-width: 576px) {
    .chat-container {
        padding: 0;
    }
    
    .chat-layout {
        height: 100vh;
        border-radius: 0;
    }
    
    .message-wrapper {
        max-width: 90%;
    }
    
    .chat-header {
        padding: 12px 16px;
    }
    
    .message-form-container {
        padding: 12px 16px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto resize textarea
    const messageInput = document.getElementById('message-input');
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Scroll to bottom of messages
    const messagesContainer = document.getElementById('messagesContainer');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // Mobile sidebar toggle (if needed)
    const backBtn = document.querySelector('.back-btn');
    const sidebar = document.querySelector('.chat-layout > div:first-child');
    
    if (window.innerWidth <= 768) {
        backBtn.addEventListener('click', function(e) {
            if (window.location.pathname.includes('/chat/')) {
                e.preventDefault();
                sidebar.classList.toggle('active');
            }
        });
    }
});
</script>
@endsection