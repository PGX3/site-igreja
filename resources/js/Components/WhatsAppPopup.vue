<template>
  <div class="whatsapp-widget">
    <!-- Caixa de Pop-up que aparece acima do botão -->
    <transition name="fade">
      <div v-if="mostrarPopup" class="whatsapp-popup">
        <div class="popup-header">
          <span>Iniciar uma conversa</span>
          <button @click="mostrarPopup = false" class="btn-fechar">&times;</button>
        </div>
        <div class="popup-body flex flex-col items-center">
          <p>Olá, como podemos ajudar?</p>
          <a :href="linkWhatsApp" target="_blank" class="btn-iniciar">
            Iniciar Chat
          </a>
        </div>
      </div>
    </transition>

    <!-- Botão Flutuante -->
    <button @click="togglePopup" class="whatsapp-btn">
      <svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.15-.197.297-.766.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.346.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.206-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a6.827 6.827 0 01-3.483-.955l-.25-.15-2.597.682.693-2.528-.162-.26a6.83 6.83 0 011.041-7.172c1.826-1.828 4.252-2.835 6.829-2.835a6.829 6.829 0 016.827 6.83c0 1.826-.71 3.541-2.007 4.829a6.812 6.812 0 01-4.824 1.995z"/>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// Insira o seu número no formato internacional: Código do país + DDD + número (Ex: 5551999999999)
const numeroTelefone = ref('5551998022153'); 
const mensagemInicial = ref('Olá! Gostaria de tirar uma dúvida.');

const mostrarPopup = ref(false);

const linkWhatsApp = computed(() => {
 return `https://wa.me/${numeroTelefone.value}?text=${encodeURIComponent(mensagemInicial.value)}`;
});

const togglePopup = () => {
  mostrarPopup.value = !mostrarPopup.value;
};
</script>

<style scoped>
.whatsapp-widget {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 99999;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  font-family: Arial, sans-serif;
}

.whatsapp-btn {
  background-color: #25D366;
  color: white;
  border: none;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.3s ease, background-color 0.3s ease;
}

.whatsapp-btn:hover {
  transform: scale(1.1);
  background-color: #128C7E;
}

.whatsapp-popup {
  background-color: white;
  color: black;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  width: 280px;
  margin-bottom: 15px;
  overflow: hidden;
  animation: slideIn 0.3s ease-out;
}

.popup-header {
  background-color: #075E54;
  color: white;
  padding: 10px 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
}

.btn-fechar {
  background: none;
  border: none;
  color: white;
  font-size: 20px;
  cursor: pointer;
}

.popup-body {
  padding: 15px;
  text-align: center;
}

.btn-iniciar {
  display: inline-block;
  background-color: #25D366;
  color: white;
  text-decoration: none;
  padding: 10px 20px;
  border-radius: 20px;
  font-weight: bold;
  margin-top: 10px;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

@keyframes slideIn {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>
