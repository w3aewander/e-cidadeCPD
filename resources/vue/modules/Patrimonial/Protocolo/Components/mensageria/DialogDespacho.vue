<script setup>
import {onMounted, ref} from 'vue';
const props = defineProps(['show', 'aviso', 'visualizarBtn']);
const emit = defineEmits(['fecharDialog', 'criarDespacho']);
const checkboxValue = ref(true);
onMounted(() => {
    checkboxValue.value = true;
});
</script>

<template>
    <div v-if="show">
        <div class="overlay"></div>
        <div class="modal">
            <div class="content">
                <p>{{ aviso }}</p>
                <label for="checkbox" class="checkbox" v-if="visualizarBtn">
                    <input type="checkbox" id="checkbox" v-model="checkboxValue">
                    Público
                </label>
                <div class="botoes" style="padding-top:10px">
                    <button @click="$emit('criarDespacho', checkboxValue)" v-if="visualizarBtn">Confirmar</button>
                    <button @click="$emit('fecharDialog')">{{ visualizarBtn ? 'Cancelar' : 'OK' }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

button {
    text-align: center;
    margin: 5px;
    background: #2a60ff;
    color: white;
    cursor: pointer;
    width: 90px;
    height: 40px;
    border-radius: 35px;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    backdrop-filter: blur(5px); /* Aplica o efeito de desfoque */
    z-index: 100;
}

.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 101;
}

.content {
    text-align: center;
}
</style>
