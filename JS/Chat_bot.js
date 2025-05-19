// Elementos del DOM
const chatbotToggle = document.getElementById('chatbot-toggle');
const chatbotContainer = document.getElementById('chatbot-container');
const closeChatbot = document.getElementById('close-chatbot');
const userInput = document.getElementById('user-input');
const sendButton = document.getElementById('send-button');
const chatMessages = document.getElementById('chatbot-messages');

// Alternar visibilidad del chatbot
chatbotToggle.addEventListener('click', () => {
    chatbotContainer.classList.toggle('active');
});

// Cerrar el chatbot
closeChatbot.addEventListener('click', () => {
    chatbotContainer.classList.remove('active');
});

// Enviar mensaje (simulado)
sendButton.addEventListener('click', sendMessage);
userInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendMessage();
});

function sendMessage() {
    const message = userInput.value.trim();
    if (message === "") return;

    // Mostrar mensaje del usuario
    const userMessage = document.createElement('div');
    userMessage.classList.add('message', 'user-message');
    userMessage.textContent = message;
    chatMessages.appendChild(userMessage);

    // Limpiar input
    userInput.value = '';

    // Scroll al final
    chatMessages.scrollTop = chatMessages.scrollHeight;

    // Respuesta automática del bot (simulada)
    setTimeout(() => {
        const botMessage = document.createElement('div');
        botMessage.classList.add('message', 'bot-message');
        botMessage.textContent = "Gracias por tu mensaje. ¿Necesitas algo más?";
        chatMessages.appendChild(botMessage);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }, 1000);
}