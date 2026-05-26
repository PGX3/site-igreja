<template>
  <div class="whatsapp-widget">
    <!-- Popup -->
    <transition name="fade">
      <div v-if="showPopup" class="whatsapp-popup">
        <div class="popup-header">
          <span>Iniciar uma conversa</span>
          <button @click="showPopup = false" class="close-btn">&times;</button>
        </div>
        <div class="popup-body">
          <p>Olá, como podemos ajudar?</p>
          <a :href="whatsappLink" target="_blank" class="start-btn">
            Iniciar Chat
          </a>
        </div>
      </div>
    </transition>

    <!-- Botão Flutuante -->
    <button @click="togglePopup" class="whatsapp-button" title="Abrir WhatsApp">
      <svg viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.15-.197.297-.766.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.346.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.206-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a6.827 6.827 0 01-3.483-.955l-.25-.15-2.597.682.693-2.528-.162-.26a6.83 6.83 0 011.041-7.172c1.826-1.828 4.252-2.835 6.829-2.835a6.829 6.829 0 016.827 6.83c0 1.826-.71 3.541-2.007 4.829a6.812 6.812 0 01-4.824 1.995z"/>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const phoneNumber = ref('5551998022153');
const initialMessage = ref('Olá! Gostaria de tirar uma dúvida.');
const showPopup = ref(false);

const whatsappLink = computed(() => {
  return `https://wa.me/${phoneNumber.value}?text=${encodeURIComponent(initialMessage.value)}`;
});

const togglePopup = () => {
  showPopup.value = !showPopup.value;
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.whatsapp-widget {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 99999;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
}

/* Botão Principal */
.whatsapp-button {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: #25D366;
  border: none;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.whatsapp-button:hover {
  background-color: #20ba5a;
  transform: scale(1.1);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.whatsapp-button:active {
  transform: scale(0.95);
}

.whatsapp-button svg {
  width: 32px;
  height: 32px;
}

/* Popup */
.whatsapp-popup {
  position: absolute;
  bottom: 80px;
  left: 0;
  right: 0;
  margin: auto;
  width: 400px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  animation: slideUp 0.3s ease-out;
}

.popup-header {
  background-color: #075E54;
  color: white;
  padding: 14px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  font-size: 14px;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s;
}

.close-btn:hover {
  opacity: 0.8;
}

.popup-body {
  padding: 16px;
  text-align: center;
}

.popup-body p {
  color: #333;
  font-size: 14px;
  margin-bottom: 12px;
  line-height: 1.5;
}

.start-btn {
  display: inline-block;
  background-color: #25D366;
  color: white;
  text-decoration: none;
  padding: 10px 24px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.3s ease;
}

.start-btn:hover {
  background-color: #20ba5a;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(37, 211, 102, 0.3);
}

/* Animações */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsivo */
@media (max-width: 480px) {
  .whatsapp-popup {
    width: 350px;
  }
}
</style>