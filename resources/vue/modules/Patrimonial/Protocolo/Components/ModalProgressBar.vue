<template>
    <transition name="modal">
        <div v-if="isLoading" class="modal-mask">
            <div class="modal-wrapper">
                <div class="progress-bar-container">
                    <span><slot>{{ message }}</slot></span>

                    <ProgressBar
                        :value="percentualProgresso"
                        :showValue="true"
                        style="width: 100%; height: 15px;"
                    />
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import ProgressBar from "primevue/progressbar";

const props = defineProps({
    isLoading: Boolean,
    message: {
        type: String,
        default: 'Carregando...'
    },
    percentualProgresso: Number
})
</script>

<style scoped>
.modal-mask {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1059;
    height: 100%;
    display: flex;
    justify-content: center;
    background-color: rgba(0, 0, 0, 0.5);
    transition: opacity 0.3s ease;
}

.modal-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.progress-bar-container {
    width: 60%;
    padding: 0 20px;
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.progress-bar-container span {
    color: var(--text-color);
    font-size: 1.3rem;
    font-weight: bold;
}

:deep(.p-progressbar) {
    border-radius: 2rem;
}
</style>
