<template>
    <i
        v-show="isVisible"
        @click="execAction"
        :class="scrollToStart ? 'back-to-top pi pi-arrow-up' : 'back-to-top pi pi-arrow-down'"
        title="Voltar ao topo"
    />
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isVisible = ref(false);
const scrollToStart= ref(false);
const scrollToEnd = ref(false);
let lastScrollY = window.scrollY;

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const scrollToBottom = () => {
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
};

const execAction = () => {
    if (scrollToStart.value) {
        scrollToTop();
    } else {
        scrollToBottom();
    }
}

const handleScroll = () => {
    const currentScrollY = window.scrollY;

    if (currentScrollY > lastScrollY) {
        isVisible.value = true;
        scrollToEnd.value = true;
        scrollToStart.value = false;
    } else {
        scrollToStart.value = true;
        scrollToEnd.value = false;
    }

    lastScrollY = currentScrollY;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.back-to-top {
    position: fixed;
    bottom: 20px;
    right: 30px;
    z-index: 99;
    border: none;
    outline: none;
    background-color: #555;
    color: white;
    cursor: pointer;
    padding: 15px;
    border-radius: 10px;
    font-size: 18px;
}
.back-to-top:hover {
    cursor:pointer;
}
.back-to-top:hover {
    background-color: #333;
}
[v-show="true"] {
    display: block !important;
}
</style>
