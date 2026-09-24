<template>
    <Toast position="center" />
    <ModalLoading :isLoading="isLoading" />
    <Fieldset
        legend="Importação de Divida Ativa Simples Nacional"
        class="m-auto mt-5 w-max shadow-4"
    >
    <div class="file-upload-container">
        <div class="file-upload-content">
            <span>Escolha um arquivo:</span>
            <FileUpload
                name="file"
                accept=".txt"
                mode="basic"
                custom-upload
                @select="onFileSelect"
            />
        </div>
        <Button label="Upload" icon="pi pi-upload" @click="submitFile" />
    </div>
    </Fieldset>
</template>

<script setup>
    import { useToast } from "primevue/usetoast";
    import { ref } from "vue";
    import ModalLoading from '../../Components/ModalLoading.vue'

    const toast = useToast();
    const file = ref(null);
    const isLoading = ref(false);

    function onFileSelect(event) {
        file.value = event.files[0];
    }

    async function submitFile() {

        if (!file.value) {
            alert('Selecione um arquivo primeiro!');
            return;
        }

        const formData = new FormData();
        formData.append('file', file.value);
        isLoading.value = true;
        try {
            await window.axios
                .post(
                    `v4/api/tributario/issqn/simples-nacional/importa-divida-ativa`,
                    formData
                ).then((data) => {
                    isLoading.value = false;           
                    toast.add({
                        severity: "success",
                        summary: "Sucesso",
                        detail: "Arquivo Importado com sucesso!",
                    });
                    return data;
                })
                .catch((erro) => {
                    isLoading.value = false;
                    toast.add({
                        severity: "error",
                        summary: "Erro",
                        detail: erro.response.data.message,
                    });
                });
        } catch (error) {
            toast.add({
                severity: "error",
                summary: "Erro",
                detail: "Algo deu errado",
            });
        }

    }
</script>

<style>
.file-upload-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.file-upload-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
</style>