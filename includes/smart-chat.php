<?php
/**
 * Widget Messagerie Intelligente & Concierge BTP - FEE BTP SARL
 * Inclus automatiquement sur toutes les pages publiques
 */
$botWhatsapp = preg_replace('/[^0-9]/', '', getSetting('whatsapp_number', '237697398455'));
$botPhone = getSetting('phone_primary', '(+237) 697 39 84 55');
$botPhoneClean = preg_replace('/[^0-9+]/', '', $botPhone);
?>

<!-- ========================================================================
     STYLE DÉDIÉ - SMART CHAT CONCIERGE BTP
     ======================================================================== -->
<style>
/* Conteneur Flottant Principal */
#feeChatWidget {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 999999;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Bouton Déclencheur Flottant */
.fee-chat-trigger-group {
  display: flex;
  align-items: center;
  gap: 12px;
  position: relative;
}

/* Bulle d'accroche proactive */
.fee-chat-teaser {
  background: #ffffff;
  color: #0c2e60;
  padding: 10px 16px;
  border-radius: 18px;
  box-shadow: 0 8px 24px rgba(12, 46, 96, 0.18);
  font-size: 13.5px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  border: 1px solid rgba(12, 46, 96, 0.08);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  animation: feeBounceIn 0.6s ease;
  max-width: 260px;
}
.fee-chat-teaser:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(12, 46, 96, 0.25);
}
.fee-chat-teaser-close {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 16px;
  cursor: pointer;
  padding: 0 4px;
  line-height: 1;
}
.fee-chat-teaser-close:hover { color: #dc2626; }

/* Bouton Principal Rond */
.fee-chat-btn-main {
  width: 62px;
  height: 62px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0c2e60 0%, #1e4287 100%);
  border: 3px solid #ffffff;
  box-shadow: 0 8px 25px rgba(12, 46, 96, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  cursor: pointer;
  position: relative;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.fee-chat-btn-main:hover {
  transform: scale(1.08) rotate(3deg);
  box-shadow: 0 12px 30px rgba(12, 46, 96, 0.45);
}
.fee-chat-btn-main i {
  font-size: 26px;
  transition: transform 0.3s;
}

/* Pastille En Ligne */
.fee-chat-online-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 15px;
  height: 15px;
  background-color: #22c55e;
  border-radius: 50%;
  border: 2px solid #ffffff;
}
.fee-chat-online-badge::after {
  content: '';
  position: absolute;
  top: -2px; left: -2px; right: -2px; bottom: -2px;
  border-radius: 50%;
  border: 2px solid #22c55e;
  animation: feePulseBadge 2s infinite;
}

/* Fenêtre de Conversation */
.fee-chat-window {
  display: none;
  width: 380px;
  max-width: calc(100vw - 32px);
  height: 570px;
  max-height: calc(100vh - 110px);
  background: #ffffff;
  border-radius: 24px;
  box-shadow: 0 16px 45px rgba(12, 46, 96, 0.28);
  border: 1px solid rgba(12, 46, 96, 0.08);
  flex-direction: column;
  overflow: hidden;
  position: absolute;
  bottom: 76px;
  right: 0;
  animation: feeSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Header du Chat */
.fee-chat-header {
  background: linear-gradient(135deg, #0c2e60 0%, #173b75 100%);
  color: #ffffff;
  padding: 16px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 2px solid rgba(249, 115, 22, 0.4);
}
.fee-chat-header-info {
  display: flex;
  align-items: center;
  gap: 12px;
}
.fee-chat-avatar {
  width: 44px;
  height: 44px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: #f97316;
  border: 1.5px solid rgba(255, 255, 255, 0.25);
  position: relative;
}
.fee-chat-header-text h5 {
  margin: 0;
  font-size: 15px;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: -0.2px;
}
.fee-chat-header-text p {
  margin: 2px 0 0;
  font-size: 11.5px;
  color: #cbd5e1;
  display: flex;
  align-items: center;
  gap: 5px;
}
.fee-chat-header-text p span {
  width: 7px;
  height: 7px;
  background: #22c55e;
  border-radius: 50%;
  display: inline-block;
}

.fee-chat-header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.fee-chat-header-btn {
  background: rgba(255, 255, 255, 0.12);
  border: none;
  color: #ffffff;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 0.2s;
  text-decoration: none;
}
.fee-chat-header-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}

/* Zone des Messages */
.fee-chat-body {
  flex: 1;
  padding: 16px;
  background: #f8fafc;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Messages */
.fee-msg-row {
  display: flex;
  gap: 8px;
  max-width: 88%;
}
.fee-msg-row.bot {
  align-self: flex-start;
}
.fee-msg-row.user {
  align-self: flex-end;
  flex-direction: row-reverse;
}
.fee-msg-bubble {
  padding: 11px 15px;
  font-size: 13.5px;
  line-height: 1.45;
  border-radius: 16px;
  word-break: break-word;
}
.fee-msg-row.bot .fee-msg-bubble {
  background: #ffffff;
  color: #1e293b;
  border-top-left-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
}
.fee-msg-row.user .fee-msg-bubble {
  background: linear-gradient(135deg, #0c2e60 0%, #1e4287 100%);
  color: #ffffff;
  border-top-right-radius: 4px;
  box-shadow: 0 4px 12px rgba(12, 46, 96, 0.2);
}

.fee-msg-time {
  font-size: 10px;
  margin-top: 4px;
  color: #94a3b8;
  text-align: right;
}

/* Suggestions & Boutons Clés (Puces Compactes Horizontales) */
.fee-chat-suggestions {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 10px;
}
.fee-suggest-btn {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  color: #0c2e60;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  text-decoration: none;
  line-height: 1.2;
}
.fee-suggest-btn:hover {
  background: #0c2e60;
  border-color: #0c2e60;
  color: #ffffff;
  transform: translateY(-1px);
}
.fee-suggest-btn.accent {
  background: #fff7ed;
  border-color: #fed7aa;
  color: #ea580c;
}
.fee-suggest-btn.accent:hover {
  background: #ea580c;
  border-color: #ea580c;
  color: #ffffff;
}

/* Indicateur de frappe */
.fee-typing-indicator {
  display: none;
  align-self: flex-start;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  padding: 8px 14px;
  border-radius: 14px;
  border-top-left-radius: 4px;
  gap: 4px;
}
.fee-typing-indicator span {
  width: 6px;
  height: 6px;
  background: #94a3b8;
  border-radius: 50%;
  animation: feeTyping 1.4s infinite ease-in-out both;
}
.fee-typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.fee-typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

/* Formulaire d'étape Devis Express */
.fee-flow-card {
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 12px;
  margin-top: 6px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.fee-flow-input {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 13px;
  outline: none;
}
.fee-flow-input:focus {
  border-color: #0c2e60;
  box-shadow: 0 0 0 3px rgba(12, 46, 96, 0.1);
}

/* Zone de Saisie Inférieure */
.fee-chat-footer {
  padding: 12px 14px;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 8px;
}
.fee-chat-input {
  flex: 1;
  border: 1px solid #cbd5e1;
  border-radius: 20px;
  padding: 10px 16px;
  font-size: 13px;
  outline: none;
  transition: border-color 0.2s;
  background: #f8fafc;
}
.fee-chat-input:focus {
  border-color: #0c2e60;
  background: #ffffff;
}
.fee-chat-send-btn {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #f97316;
  border: none;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  cursor: pointer;
  transition: transform 0.2s, background-color 0.2s;
}
.fee-chat-send-btn:hover {
  background: #ea580c;
  transform: scale(1.06);
}

/* Animations */
@keyframes feePulseBadge {
  0% { transform: scale(1); opacity: 1; }
  100% { transform: scale(2.4); opacity: 0; }
}
@keyframes feeSlideUp {
  from { opacity: 0; transform: translateY(20px) scale(0.96); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes feeBounceIn {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}
@keyframes feeTyping {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1); }
}

/* Responsive Téléphone */
@media (max-width: 480px) {
  #feeChatWidget {
    bottom: 16px;
    right: 16px;
  }
  .fee-chat-window {
    width: calc(100vw - 32px);
    height: 82vh;
    bottom: 70px;
  }
  .fee-chat-teaser {
    display: none; /* masqué sur très petit écran pour ne pas masquer le contenu */
  }
}
</style>

<!-- ========================================================================
     STRUCTURE HTML - SMART CHAT CONCIERGE BTP
     ======================================================================== -->
<div id="feeChatWidget">
  
  <!-- 1. Fenêtre de Discussion Interactive -->
  <div class="fee-chat-window" id="feeChatWindow">
    <!-- Header -->
    <div class="fee-chat-header">
      <div class="fee-chat-header-info">
        <div class="fee-chat-avatar" style="overflow: hidden; background: #ffffff; padding: 2px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.12);">
          <img src="images/logo.png" alt="Logo RMC BTP" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <div class="fee-chat-header-text">
          <h5>Conseiller IA RMC BTP SARL</h5>
          <p><span></span> En ligne • Douala, Yaoundé & Diaspora</p>
        </div>
      </div>
      <div class="fee-chat-header-actions">
        <!-- Raccourci WhatsApp Direct -->
        <a href="https://wa.me/<?= $botWhatsapp ?>?text=Bonjour%20RMC%20BTP%20SARL,%20je%20souhaite%20un%20renseignement%20technique." 
            target="_blank" 
            class="fee-chat-header-btn" 
            title="Continuer sur WhatsApp" 
            style="background: #25D366; color: #fff;">
          <i class="fab fa-whatsapp"></i>
        </a>
        <!-- Raccourci Appel Direct -->
        <a href="tel:<?= $botPhoneClean ?>" class="fee-chat-header-btn" title="Appeler la direction">
          <i class="fa fa-phone"></i>
        </a>
        <!-- Fermer / Réduire -->
        <button class="fee-chat-header-btn" id="feeChatClose" title="Fermer le chat">
          <i class="fa fa-times"></i>
        </button>
      </div>
    </div>

    <!-- Corps de Discussion -->
    <div class="fee-chat-body" id="feeChatBody">
      <!-- Les messages seront insérés dynamiquement ici -->
      
      <!-- Indicateur de frappe -->
      <div class="fee-typing-indicator" id="feeTypingIndicator">
        <span></span><span></span><span></span>
      </div>
    </div>

    <!-- Footer / Zone de Saisie -->
    <div class="fee-chat-footer">
      <input type="text" class="fee-chat-input" id="feeChatInput" placeholder="Posez votre question (ex: devis, localisation...)" autocomplete="off">
      <button class="fee-chat-send-btn" id="feeChatSend" title="Envoyer">
        <i class="fa fa-paper-plane"></i>
      </button>
    </div>
  </div>

  <!-- 2. Déclencheur Flottant (Teaser + Bouton Principal) -->
  <div class="fee-chat-trigger-group">
    <!-- Bulle Teaser proactive -->
    <div class="fee-chat-teaser" id="feeChatTeaser">
      <span>💬 Un projet de construction ou de rénovation ? Discutons en direct !</span>
      <button class="fee-chat-teaser-close" id="feeChatTeaserClose">&times;</button>
    </div>

    <!-- Bouton Rond -->
    <div class="fee-chat-btn-main" id="feeChatToggle" title="Ouvrir la messagerie FEE BTP SARL">
      <div class="fee-chat-online-badge"></div>
      <i class="fa fa-comments" id="feeChatToggleIcon"></i>
    </div>
  </div>

</div>

<!-- ========================================================================
     SCRIPT JAVASCRIPT - LOGIQUE INTELLIGENTE DU CONCIERGE BTP
     ======================================================================== -->
<script>
(function() {
  const chatWindow = document.getElementById('feeChatWindow');
  const chatToggle = document.getElementById('feeChatToggle');
  const chatToggleIcon = document.getElementById('feeChatToggleIcon');
  const chatClose = document.getElementById('feeChatClose');
  const chatBody = document.getElementById('feeChatBody');
  const chatInput = document.getElementById('feeChatInput');
  const chatSend = document.getElementById('feeChatSend');
  const chatTeaser = document.getElementById('feeChatTeaser');
  const chatTeaserClose = document.getElementById('feeChatTeaserClose');
  const typingIndicator = document.getElementById('feeTypingIndicator');

  // Données de session de qualification
  let isChatInitialized = false;
  let chatHistoryText = "";
  let leadData = {
    service: '',
    location: '',
    details: '',
    name: '',
    phone: ''
  };

  // Fermer le teaser
  if (chatTeaserClose) {
    chatTeaserClose.addEventListener('click', function(e) {
      e.stopPropagation();
      chatTeaser.style.display = 'none';
    });
  }

  // Clic sur le teaser = ouvre le chat
  if (chatTeaser) {
    chatTeaser.addEventListener('click', function() {
      openChat();
    });
  }

  // Ouvrir / Fermer le chat
  chatToggle.addEventListener('click', function() {
    if (chatWindow.style.display === 'flex') {
      closeChat();
    } else {
      openChat();
    }
  });

  chatClose.addEventListener('click', function() {
    closeChat();
  });

  function openChat() {
    chatWindow.style.display = 'flex';
    if (chatTeaser) chatTeaser.style.display = 'none';
    chatToggleIcon.className = 'fa fa-times';
    if (!isChatInitialized) {
      initChat();
    }
    setTimeout(() => {
      chatInput.focus();
      scrollBottom();
    }, 200);
  }

  function closeChat() {
    chatWindow.style.display = 'none';
    chatToggleIcon.className = 'fa fa-comments';
  }

  function scrollBottom() {
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  function formatTime() {
    const d = new Date();
    return ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
  }

  // Ajouter un message dans le flux
  function appendMessage(sender, text, suggestions = [], flowCard = null) {
    const row = document.createElement('div');
    row.className = 'fee-msg-row ' + sender;

    const bubble = document.createElement('div');
    bubble.className = 'fee-msg-bubble';
    
    // Markdown basique (**gras**, \n saut de ligne)
    let formattedText = text
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\n/g, '<br>');
    
    bubble.innerHTML = formattedText;

    // Horodatage
    const timeSpan = document.createElement('div');
    timeSpan.className = 'fee-msg-time';
    timeSpan.innerText = formatTime();
    bubble.appendChild(timeSpan);

    // Ajout suggestions si présentes
    if (suggestions && suggestions.length > 0) {
      const suggestContainer = document.createElement('div');
      suggestContainer.className = 'fee-chat-suggestions';
      
      suggestions.forEach(s => {
        if (s.url) {
          const a = document.createElement('a');
          a.className = 'fee-suggest-btn accent';
          a.href = s.url;
          a.target = '_blank';
          a.innerHTML = s.label + ' <i class="fa fa-external-link-alt ml-1"></i>';
          suggestContainer.appendChild(a);
        } else {
          const btn = document.createElement('button');
          btn.className = 'fee-suggest-btn';
          btn.innerHTML = s.label;
          btn.addEventListener('click', function() {
            handleSuggestionClick(s.action, s.label);
          });
          suggestContainer.appendChild(btn);
        }
      });
      bubble.appendChild(suggestContainer);
    }

    // Ajout formulaire conversationnel si présent
    if (flowCard) {
      bubble.appendChild(flowCard);
    }

    row.appendChild(bubble);

    // Insérer avant l'indicateur de frappe
    chatBody.insertBefore(row, typingIndicator);
    scrollBottom();

    // Trace dans l'historique
    chatHistoryText += (sender === 'bot' ? 'Assistant: ' : 'Client: ') + text + "\n";
  }

  function showTyping(show) {
    typingIndicator.style.display = show ? 'flex' : 'none';
    scrollBottom();
  }

  // Initialisation avec l'API
  function initChat() {
    isChatInitialized = true;
    showTyping(true);

    fetch('api-chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'get_welcome' })
    })
    .then(res => res.json())
    .then(data => {
      showTyping(false);
      if (data.status === 'success') {
        appendMessage('bot', data.reply, data.suggestions);
      }
    })
    .catch(() => {
      showTyping(false);
      appendMessage('bot', "👋 Bonjour ! Bienvenue chez **RMC BTP SARL**. Que pouvons-nous réaliser pour votre projet ?", [
        { label: '🏗️ Estimer mon projet (Devis Express)', action: 'flow_devis' },
        { label: '💬 Échanger sur WhatsApp', action: 'contact_whatsapp' }
      ]);
    });
  }

  // Traitement d'un clic de suggestion
  function handleSuggestionClick(action, label) {
    appendMessage('user', label);

    if (action === 'flow_devis') {
      startDevisFlow();
      return;
    }

    if (action === 'contact_whatsapp') {
      window.open('https://wa.me/<?= $botWhatsapp ?>?text=Bonjour%20FEE%20BTP%20SARL,%20je%20souhaite%20des%20renseignements%20sur%20vos%20travaux.', '_blank');
      showTyping(true);
      setTimeout(() => {
        showTyping(false);
        appendMessage('bot', "Je vous ai redirigé vers la ligne WhatsApp officielle de M. Fabrice AGHOKENG. Souhaitez-vous poser une autre question ici ?", [
          { label: '🏗️ Estimer un autre projet', action: 'flow_devis' }
        ]);
      }, 700);
      return;
    }

    if (action === 'contact_call') {
      window.location.href = 'tel:<?= $botPhoneClean ?>';
      return;
    }

    if (action === 'reset_chat') {
      leadData = { service: '', location: '', details: '', name: '', phone: '' };
      initChat();
      return;
    }

    if (action === 'set_service') {
      leadData.service = label.replace(/^[^\wÀ-ÿ]+/i, '').trim();
      askLocation();
      return;
    }

    if (action === 'set_location') {
      leadData.location = label.replace(/^[^\wÀ-ÿ]+/i, '').trim();
      askContactForm();
      return;
    }

    // Appel API pour réponse sémantique
    callApi(label);
  }

  // Appel API général
  function callApi(messageText) {
    showTyping(true);

    fetch('api-chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'message', message: messageText, chat_history: chatHistoryText })
    })
    .then(res => res.json())
    .then(data => {
      showTyping(false);
      if (data.status === 'success') {
        appendMessage('bot', data.reply, data.suggestions);
      }
    })
    .catch(() => {
      showTyping(false);
      appendMessage('bot', "Désolé, une petite erreur réseau s'est produite. Vous pouvez nous joindre directement au **<?= $botPhone ?>** ou sur WhatsApp.", [
        { label: '💬 Ouvrir WhatsApp', action: 'contact_whatsapp' }
      ]);
    });
  }

  // Envoi de message utilisateur libre
  function sendUserMessage() {
    const text = chatInput.value.trim();
    if (!text) return;

    chatInput.value = '';
    appendMessage('user', text);
    callApi(text);
  }

  chatSend.addEventListener('click', sendUserMessage);
  chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      sendUserMessage();
    }
  });

  // ========================================================================
  // PARCOURS INTERACTIF : DEVIS EXPRESS BTP PAS-À-PAS
  // ========================================================================
  function startDevisFlow() {
    showTyping(true);
    setTimeout(() => {
      showTyping(false);
      appendMessage('bot', "Excellent choix ! Configurons votre estimation en 3 petites étapes.\n\n👉 **Étape 1/3 : Quel est le type de votre projet ?**", [
        { label: '🏠 Villa individuelle / Duplex neuf', action: 'set_service' },
        { label: '🧱 Gros Œuvre seul (Fondations, Dalle, Murs)', action: 'set_service' },
        { label: '🎨 Rénovation complète / Finitions / Carrelage', action: 'set_service' },
        { label: '⛰️ Mur de soutènement & Aménagement de pente', action: 'set_service' },
        { label: '📐 Plans architecturaux & Permis de bâtir', action: 'set_service' }
      ]);
    }, 350);
  }

  function askLocation() {
    showTyping(true);
    setTimeout(() => {
      showTyping(false);
      appendMessage('bot', "Très bien pour **" + leadData.service + "** !\n\n👉 **Étape 2/3 : Où se situe le chantier ?**", [
        { label: '📍 Douala (Bonapriso, Bonamoussadi, Makepe...)', action: 'set_location' },
        { label: '📍 Yaoundé (Bastos, Odza, Simbock...)', action: 'set_location' },
        { label: '📍 Dschang / Bafoussam & Région Ouest', action: 'set_location' },
        { label: '📍 Autre ville au Cameroun', action: 'set_location' }
      ]);
    }, 350);
  }

  function askContactForm() {
    showTyping(true);
    setTimeout(() => {
      showTyping(false);

      const card = document.createElement('div');
      card.className = 'fee-flow-card';
      card.innerHTML = `
        <div style="font-weight: 700; font-size: 13px; color: #0c2e60; margin-bottom: 4px;">
          <i class="fa fa-user-check mr-1" style="color: #f97316;"></i> Étape 3/3 : Vos coordonnées pour le chiffrage
        </div>
        <input type="text" id="feeLeadName" class="fee-flow-input" placeholder="Votre Nom complet *" required>
        <input type="tel" id="feeLeadPhone" class="fee-flow-input" placeholder="Numéro WhatsApp / Téléphone *" required>
        <textarea id="feeLeadDetails" class="fee-flow-input" rows="2" placeholder="Détails (ex: terrain de 400m², pente, budget estimé...)"></textarea>
        <button id="feeLeadSubmit" class="btn btn-primary btn-sm font-weight-bold py-2 mt-1" style="background: linear-gradient(135deg, #0c2e60, #1e4287); border: none; border-radius: 8px;">
          <i class="fa fa-check mr-1"></i> Valider et recevoir mon devis
        </button>
      `;

      appendMessage('bot', "Dernière étape ! Veuillez renseigner votre contact afin que notre bureau d'études technique vous transmette le devis et planifie un échange.", [], card);

      const submitBtn = card.querySelector('#feeLeadSubmit');
      submitBtn.addEventListener('click', function() {
        const nameInput = card.querySelector('#feeLeadName').value.trim();
        const phoneInput = card.querySelector('#feeLeadPhone').value.trim();
        const detailsInput = card.querySelector('#feeLeadDetails').value.trim();

        if (!nameInput || !phoneInput) {
          alert('Veuillez renseigner au moins votre nom et votre numéro de téléphone.');
          return;
        }

        leadData.name = nameInput;
        leadData.phone = phoneInput;
        leadData.details = detailsInput;

        submitLeadData();
      });

    }, 400);
  }

  function submitLeadData() {
    showTyping(true);

    fetch('api-chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'save_lead',
        name: leadData.name,
        phone: leadData.phone,
        service: leadData.service,
        location: leadData.location,
        details: leadData.details,
        chat_history: chatHistoryText
      })
    })
    .then(res => res.json())
    .then(data => {
      showTyping(false);
      if (data.status === 'success') {
        appendMessage('bot', data.reply, data.suggestions);
      } else {
        appendMessage('bot', "Une erreur s'est produite lors de l'enregistrement. Vous pouvez nous écrire directement sur WhatsApp au **<?= $botPhone ?>**.", [
          { label: '💬 Contacter sur WhatsApp', action: 'contact_whatsapp' }
        ]);
      }
    })
    .catch(() => {
      showTyping(false);
      appendMessage('bot', "Votre demande a été notée. Nous vous invitons à poursuivre sur WhatsApp avec M. AGHOKENG pour confirmation immédiate.", [
        { label: '💬 Ouvrir WhatsApp', action: 'contact_whatsapp' }
      ]);
    });
  }

})();
</script>
