<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Service Portal</title>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>
    <style>
        <?php require __DIR__ . '/styles/CS1.css'; ?>
    </style>
</head>
<body>

    <div class="main-container">
        <!-- Left Panel - Navigation -->
        <div class="left-panel">
            <div class="logo">CS Portal</div>

            <div class="nav-menu">
                <div class="nav-item active">Chat Assistant</div>
            </div>

            <a href="customer_homepage.php" class="back-btn">← Dashboard</a>
        </div>

        <!-- Right Panel - Content -->
        <div class="right-panel">
            <!-- Chatbot Panel -->
            <div class="chat-panel" id="chatbot-panel">
                <div class="panel-header">
                    Customer Service
                </div>
                <div class="chatbot-messages" id="chatbotMessages">
                    <div class="message bot-message">
                        Hello! I'm Customer Service. How can I help you today?
                    </div>
                </div>
                <div class="chatbot-input-container">
                    <input type="text" class="chatbot-input" id="chatbotInput" placeholder="Type your message..." autocomplete="off" maxlength="1000">
                    <button class="send-button" id="sendButton">Send</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const chatbotInput = document.getElementById('chatbotInput');
        const sendButton = document.getElementById('sendButton');
        const chatbotMessages = document.getElementById('chatbotMessages');

        // Conversation memory: sent to the server with every message
        const history = [];

        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;

            if (sender === 'bot') {
                // Parse markdown, then sanitize so the output is safe
                messageDiv.innerHTML = DOMPurify.sanitize(marked.parse(text));
            } else {
                messageDiv.textContent = text;
            }

            chatbotMessages.appendChild(messageDiv);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }

        async function sendMessage() {
            const message = chatbotInput.value.trim();
            if (!message || sendButton.disabled) return;

            addMessage(message, 'user');
            history.push({ role: 'user', content: message });
            chatbotInput.value = '';
            sendButton.disabled = true;

            // Typing indicator
            const typingDiv = document.createElement('div');
            typingDiv.className = 'typing-indicator';
            typingDiv.innerHTML = '<span></span><span></span><span></span>';
            chatbotMessages.appendChild(typingDiv);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;

            try {
                const response = await fetch('chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ messages: history })
                });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.error?.message || data.error || response.status);
                }

                const botResponse = data.choices?.[0]?.message?.content || 'No response received.';
                history.push({ role: 'assistant', content: botResponse });
                addMessage(botResponse, 'bot');
            } catch (error) {
                history.pop(); // remove the failed message from memory
                addMessage('Sorry, I encountered an error. Please try again later.', 'bot');
                console.error('API Error:', error);
            } finally {
                typingDiv.remove();
                sendButton.disabled = false;
                chatbotInput.focus();
            }
        }

        sendButton.addEventListener('click', sendMessage);
        chatbotInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') sendMessage();
        });
    </script>
</body>
</html>